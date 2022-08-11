<?php /** @noinspection PhpUnused */

namespace YandexPay\Pay\Delivery\Yandex;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Shipment;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay\Delivery\Yandex\Api;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity as TradingEntity;

if (!Main\Loader::includeModule('sale')) { return; }

class RequestHandler extends Delivery\Requests\HandlerBase
{
	use Concerns\HasMessage;

	/** @var \YandexPay\Pay\Trading\Entity\Reference\Environment */
	protected $environment;

	public const CANCEL_ACTION_CODE = 'cancel';
	public const RENEW_ACTION_CODE = 'renew';
	public const ACCEPT_ACTION_CODE = 'accept';
	public const CREATE_ACTION_CODE = 'create';

	public const CREATED = 'CREATED'; //Идет вычисление стоимости доставки
	public const ESTIMATING_STATUS = 'ESTIMATING'; //Идет вычисление стоимости доставки
	public const EXPIRED_STATUS = 'EXPIRED'; //Доставка не подтверждена за отведенное время
	public const READY_FOR_APPROVAL_STATUS = 'READY_FOR_APPROVAL'; //Доставка ждет подтверждения
	public const COLLECTING_STATUS = 'COLLECTING'; //Идет процесс получения заказа службой доставки от продавца
	public const PREPARING_STATUS = 'PREPARING'; //Идет подготовка к отправке покупателю
	public const DELIVERING_STATUS = 'DELIVERING'; //Заказ доставляется покупателю
	public const DELIVERED_STATUS = 'DELIVERED'; //Заказ доставлен
	public const RETURNING_STATUS = 'RETURNING'; //Заказ возвращается обратно продавцу
	public const RETURNED_STATUS = 'RETURNED'; //Заказ возвращен продавцу
	public const FAILED_STATUS = 'FAILED'; //Доставка завершилась ошибкой
	public const CANCELLED_STATUS = 'CANCELLED'; //Доставка отменена продавцом

	public const FREE_STATE_CANCEL = 'FREE'; //бесплатно
	public const PAID_STATE_CANCEL = 'PAID'; //платно
	public const UNAVAILABLE_STATE_CANCEL = 'UNAVAILABLE'; //недоступно

	protected $waitCreatedExternalIds = [];
	protected $alreadyBindRequestTableCreated = false;

	public function __construct(Delivery\Services\Base $deliveryService)
	{
		parent::__construct($deliveryService);

		$this->environment = TradingEntity\Registry::getEnvironment();
	}

	public function create(array $shipmentIds, array $additional = array()) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		foreach ($shipmentIds as $shipmentId)
		{
			try
			{
				$orderId = $this->getOrderIdByShipmentId($shipmentId);

				$this->sendRequest(
					$orderId,
					new Api\Create\Request(),
					Api\Create\Response::class
				);

				$shipmentResult = new Delivery\Requests\ShipmentResult($shipmentId);

				$requestResult = new Delivery\Requests\RequestResult();
				$requestResult->setExternalId($orderId);
				$requestResult->addResult($shipmentResult);

				$result->addResult($requestResult);

				$this->waitRequestInstalled($orderId);
			}
			catch (Main\SystemException $exception)
			{
				$result->addError(new Main\Error($exception->getMessage()));
			}
		}

		return $result;
	}

	protected function waitRequestInstalled(int $externalId) : void
	{
		$this->waitCreatedExternalIds[] = $externalId;
		$this->bindRequestTableCreated();
	}

	protected function bindRequestTableCreated() : void
	{
		if ($this->alreadyBindRequestTableCreated !== false) { return; }

		$this->alreadyBindRequestTableCreated = true;

		Main\EventManager::getInstance()->addEventHandler(
			'sale',
			'\Bitrix\Sale\Delivery\Requests\Request::onAfterAdd',
			[$this, 'onRequestTableCreated']
		);
	}

	public function onRequestTableCreated(Main\Event $event) : void
	{
		$fields = $event->getParameter('fields');

		$externalId = isset($fields['EXTERNAL_ID']) ? (int)$fields['EXTERNAL_ID'] : null;

		if ($externalId === null) { return; }

		$waitIndex = array_search($externalId, $this->waitCreatedExternalIds, true);

		if ($waitIndex === false) { return; }

		array_splice($this->waitCreatedExternalIds, $waitIndex, 1);

		Internals\RepositoryTable::add([
			'REQUEST_ID' => $event->getParameter('id'),
			'STATUS' => static::CREATED,
		]);
	}

	public function notifyStatus(int $requestId, string $status) : void
	{
		$transport = $this->getTransport($requestId);
		$transport->setStatus($status);
		$transport->save();
	}

	protected function getDataForRequest(int $orderId) : array
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var \Bitrix\Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();
		$order = $orderClassName::load($orderId);

		if ($order === null)
		{
			throw new Main\SystemException('order not found');
		}

		$targetPayment = null;

		/** @var Sale\Payment $payment */
		foreach ($order->getPaymentCollection() as $payment)
		{
			if ($payment->isInner()) { continue; }

			$paySystem = $payment->getPaySystem();

			if ($paySystem !== null && $paySystem->getField('ACTION_FILE') === 'yandexpay')
			{
				$targetPayment = $payment;
				break;
			}
		}

		if ($targetPayment === null)
		{
			throw new Main\SystemException('not payment');
		}

		/** @var \Sale\Handlers\PaySystem\YandexPayHandler $handler */
		$handler = $this->environment->getPaySystem()->getHandler($targetPayment->getPaymentSystemId());

		Assert::typeOf($handler, YandexPayHandler::class, 'not YandexPayHandler');

		return [$handler->isTestMode($targetPayment), $handler->getApiKey($targetPayment)];
	}

	protected function getOrderIdByShipmentId(int $shipmentId) : ?int
	{
		$result = null;

		$query = Shipment::getList([
			'filter' => [
				'=ID' => $shipmentId,
			],
			'limit' => 1,
			'select' => [ 'ORDER_ID', 'ID' ],
		]);

		if ($shipment = $query->fetch())
		{
			$result = $shipment['ORDER_ID'];
		}

		if ($result === null)
		{
			throw new Main\SystemException('not found order id');
		}

		return $result;
	}

	public function getActions($requestId) : array
	{
		return [
			self::CANCEL_ACTION_CODE => static::getMessage('ACTION_CANCEL'),
			self::RENEW_ACTION_CODE => static::getMessage('ACTION_RENEW'),
			self::ACCEPT_ACTION_CODE => static::getMessage('ACTION_ACCEPT'),
		];
	}

	public function getShipmentActions(Shipment $shipment) : array
	{
		return [
			self::CANCEL_ACTION_CODE => static::getMessage('ACTION_CANCEL'),
			self::RENEW_ACTION_CODE => static::getMessage('ACTION_RENEW'),
			self::ACCEPT_ACTION_CODE => static::getMessage('ACTION_ACCEPT'),
		];
	}

	public function executeShipmentAction($requestId, $shipmentId, $actionType, array $additional) : Delivery\Requests\Result
	{
		return $this->executeAction($requestId, $actionType, $additional);
	}

	public function executeAction($requestId, $actionType, array $additional) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		try
		{
			$request = Sale\Delivery\Requests\RequestTable::getById($requestId)->fetch();

			if (!$request)
			{
				throw new Main\SystemException('not found transport request');
			}

			$orderId = $request['EXTERNAL_ID'];
			$transportStatus = $this->getTransport($requestId)->getStatus();

			if ($actionType === static::ACCEPT_ACTION_CODE)
			{
				if ($transportStatus !== static::READY_FOR_APPROVAL_STATUS)
				{
					throw new Main\SystemException(static::getMessage('ACCEPT_UNAVAILABLE'));
				}

				$this->sendRequest(
					$orderId,
					new Api\Accept\Request(),
					Api\Accept\Response::class
				);
			}
			else if ($actionType === static::RENEW_ACTION_CODE)
			{
				if ($transportStatus !== static::EXPIRED_STATUS)
				{
					throw new Main\SystemException(static::getMessage('RENEW_UNAVAILABLE'));
				}

				$this->sendRequest(
					$orderId,
					new Api\Renew\Request(),
					Api\Renew\Response::class
				);
			}
			else if ($actionType === static::CANCEL_ACTION_CODE)
			{
				$state = $additional['CANCEL_STATE'];

				if ($state === static::UNAVAILABLE_STATE_CANCEL)
				{
					throw new Main\SystemException(static::getMessage('CANCEL_UNAVAILABLE'));
				}

				$request = new Api\Cancel\Request();
				$request->setCancelState($additional['CANCEL_STATE']);

				$this->sendRequest($orderId, $request, Api\Cancel\Response::class);
			}
			else
			{
				throw new Main\ArgumentException(sprintf('unknown %s action', $actionType));
			}
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	protected function getTransport(int $requestId) : Internals\Model
	{
		$query = Internals\RepositoryTable::getList([
			'filter' => [ '=REQUEST_ID' => $requestId ],
			'limit' => 1,
		]);

		$transport = $query->fetchObject();

		Assert::notNull($transport, '$transport');

		return $transport;
	}

	/**
	 * @template T
	 *
	 * @param int $orderId
	 * @param Api\Reference\Request $request
	 * @param class-string<T> $responseClass
	 *
	 * @return T
	 */
	protected function sendRequest(
		int $orderId,
		Api\Reference\Request $request,
		string $responseClass
	)
	{
		[$isTestMode, $apiKey] = $this->getDataForRequest($orderId);

		$request->setTestMode($isTestMode);
		$request->setApiKey($apiKey);
		$request->setOrderId($orderId);

		return $request->buildResponse($request->send(), $responseClass);
	}

	/**
	 * @param $requestId
	 * @return Delivery\Requests\Result
	 */
	public function cancelRequest($requestId): Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		return $result;
	}

	public function getFormFields($formFieldsType, array $shipmentIds, array $additional = array()) : array
	{
		$result = [];

		if (
			$formFieldsType === Delivery\Requests\Manager::FORM_FIELDS_TYPE_ACTION
			&& $additional['ACTION_TYPE'] === static::CANCEL_ACTION_CODE
		)
		{
			$request = Main\Context::getCurrent()->getRequest();
			$requestId = $request->get('requestId');

			if ($requestId === null) { return $result; }

			$deliveryRequest = Sale\Delivery\Requests\RequestTable::getById($requestId)->fetch();
			$orderId = $deliveryRequest['EXTERNAL_ID'];

			$cancelReponse = $this->sendRequest(
				$orderId,
				new Api\CancelInfo\Request(),
				Api\CancelInfo\Response::class
			);

			$result = [
				'CANCEL_STATE' => [
					'TYPE' => 'ENUM',
					'TITLE' => self::getMessage('CANCEL_INFO_TITLE'),
					'VALUE' => $cancelReponse->getCancelState(),
					'OPTIONS' => $this->getCancelStates(),
					'DISABLED' => 'Y',
				]
			];
		}

		return $result;
	}

	protected function getCancelStates() : array
	{
		return [
			static::FREE_STATE_CANCEL => static::getMessage('CANCEL_INFO_FREE'),
			static::PAID_STATE_CANCEL => static::getMessage('CANCEL_INFO_PAID'),
			static::UNAVAILABLE_STATE_CANCEL => static::getMessage('CANCEL_INFO_UNAVAILABLE'),
		];
	}

	public function delete($requestId) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();
		$transport = $this->getTransport($requestId);

		$deleteResult = Internals\RepositoryTable::delete($transport->getId());

		if (!$deleteResult->isSuccess())
		{
			$result->addError(new Main\Error(implode(PHP_EOL, $deleteResult->getErrorMessages())));
		}

		return $result;
	}

	public function hasCallbackTrackingSupport(): bool
	{
		return true;
	}

	public function getContent($requestId) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		try
		{
			$fields = [];

			$transport = $this->getTransport($requestId);
			$values = [
				'STATUS' => $transport->getStatus(),
				'TIMESTAMP_X' => $transport->getTimestampX(),
			];

			foreach ($values as $code => $value)
			{
				if (empty($value)) { continue; }

				if ($code === 'STATUS')
				{
					$value = static::getMessage('STATUS_' . $value);
				}

				$fields[] = [
					'TITLE' => static::getMessage($code . '_TITLE'),
					'VALUE' => $value,
				];
			}

			$result->setData($fields);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error('')); //todo text
		}

		return $result;
	}
}
