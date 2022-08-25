<?php /** @noinspection PhpUnused */

namespace YandexPay\Pay\Delivery\Yandex;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Currency;
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

	/** @var TradingEntity\Reference\Environment */
	protected $environment;
	/** @var array  */
	protected $waitCreatedExternalData = [];
	/** @var bool  */
	protected $alreadyBindRequestTableCreated = false;
	/** @var array */
	protected $transport = [];

	public const CANCEL_ACTION_CODE = 'cancel';
	public const RENEW_ACTION_CODE = 'renew';
	public const ACCEPT_ACTION_CODE = 'accept';
	public const CREATE_ACTION_CODE = 'create';

	public const CREATED = 'CREATED'; // The delivery request has been sent
	public const ESTIMATING_STATUS = 'ESTIMATING'; // The cost of delivery is being calculated
	public const EXPIRED_STATUS = 'EXPIRED'; // Delivery is not confirmed in the allotted time
	public const READY_FOR_APPROVAL_STATUS = 'READY_FOR_APPROVAL'; // Delivery is waiting for confirmation
	public const COLLECTING_STATUS = 'COLLECTING'; // There is a process of receiving an order by the delivery service from the seller
	public const PREPARING_STATUS = 'PREPARING'; // Preparations are underway to ship to the buyer
	public const DELIVERING_STATUS = 'DELIVERING'; // The order is delivered to the buyer
	public const DELIVERED_STATUS = 'DELIVERED'; // The order has been delivered
	public const RETURNING_STATUS = 'RETURNING'; // The order is returned to the seller
	public const RETURNED_STATUS = 'RETURNED'; // The order has been returned to the seller
	public const FAILED_STATUS = 'FAILED'; // Delivery failed with an error
	public const CANCELLED_STATUS = 'CANCELLED'; // Delivery canceled by the seller

	public const FREE_STATE_CANCEL = 'FREE';
	public const PAID_STATE_CANCEL = 'PAID';
	public const UNAVAILABLE_STATE_CANCEL = 'UNAVAILABLE';

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

				$response = $this->sendRequest(
					$orderId,
					new Api\Create\Request(),
					Api\Create\Response::class
				);

				$shipmentResult = new Delivery\Requests\ShipmentResult($shipmentId);

				$requestResult = new Delivery\Requests\RequestResult();
				$requestResult->setExternalId($orderId);
				$requestResult->addResult($shipmentResult);

				$result->addResult($requestResult);

				$this->waitRequestInstalled($orderId, [
					'PAYLOAD' => $response->getDeliveryData(),
					'CONFIRM' => $additional['CONFIRM'],
				]);
			}
			catch (Main\SystemException $exception)
			{
				$result->addError(new Main\Error($exception->getMessage()));
			}
		}

		return $result;
	}

	protected function waitRequestInstalled(int $externalId, array $data) : void
	{
		$this->waitCreatedExternalData[$externalId] = $data;
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

		if (!isset($this->waitCreatedExternalData[$externalId])) { return; }

		$data = $this->waitCreatedExternalData[$externalId];

		Internals\RepositoryTable::add([
			'REQUEST_ID' => $event->getParameter('id'),
			'STATUS' => static::CREATED,
		] + $data);

		unset($this->waitCreatedExternalData[$externalId]);
	}

	public function notifyTransport(int $requestId, string $status, int $orderId, int $shipmentId) : void
	{
		$transport = $this->getTransport($requestId);
		$transport->setStatus($status);

		if ($status === static::READY_FOR_APPROVAL_STATUS && $transport->getConfirm())
		{
			$response = $this->sendRequest(
				$orderId,
				new Api\Accept\Request(),
				Api\Accept\Response::class
			);

			$transport->setPayload($response->getDeliveryData());
		}

		$transport->save();

		$message = $this->createMessage('process', static::getMessage('STATUS_' . $status), 'process');
		$this->sendMessage('MANAGER', $message, $requestId, $shipmentId);
	}

	protected function getDataForRequest(int $orderId) : array
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClassName */
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

	protected function getCancelStates() : array
	{
		return [
			static::FREE_STATE_CANCEL => static::getMessage('CANCEL_INFO_FREE'),
			static::PAID_STATE_CANCEL => static::getMessage('CANCEL_INFO_PAID'),
			static::UNAVAILABLE_STATE_CANCEL => static::getMessage('CANCEL_INFO_UNAVAILABLE'),
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
			$request = Delivery\Requests\RequestTable::getById($requestId)->fetch();

			if (!$request)
			{
				throw new Main\SystemException('not found transport request');
			}

			$orderId = $request['EXTERNAL_ID'];
			$transport = $this->getTransport($requestId);
			$transportStatus = $transport->getStatus();

			if ($actionType === static::ACCEPT_ACTION_CODE)
			{
				if ($transportStatus !== static::READY_FOR_APPROVAL_STATUS)
				{
					throw new Main\SystemException(static::getMessage('ACCEPT_UNAVAILABLE'));
				}

				$response = $this->sendRequest(
					$orderId,
					new Api\Accept\Request(),
					Api\Accept\Response::class
				);

				$transport->setPayload($response->getDeliveryData());
				$transport->save();
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
				$request->setCancelState($state);

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
		if (!isset($this->transport[$requestId]))
		{
			$this->transport[$requestId] = $this->loadTransport($requestId);
		}

		return $this->transport[$requestId];
	}

	protected function loadTransport(int $requestId) : Internals\Model
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

			$deliveryRequest = Delivery\Requests\RequestTable::getById($requestId)->fetch();
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
		else if ($formFieldsType === Delivery\Requests\Manager::FORM_FIELDS_TYPE_CREATE)
		{
			$result = [
				'CONFIRM' => [
					'TYPE' => 'ENUM',
					'TITLE' => self::getMessage('CONFIRM_TITLE'),
					'OPTIONS' => [
						'N' => self::getMessage('CONFIRM_N'),
						'Y' => self::getMessage('CONFIRM_Y'),
					],
					'VALUE' => 'Y',
				]
			];
		}

		return $result;
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
			$payload = $transport->getPayload();
			$values = [
				'STATUS' => $transport->getStatus(),
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

			if (!empty($payload))
			{
				foreach ($payload as $code => $value)
				{
					if ($code === 'PRICE' || $code === 'ACTUAL_PRICE')
					{
						$value = \CCurrencyLang::CurrencyFormat($value, Currency\CurrencyManager::getBaseCurrency());
						$value = html_entity_decode($value); // double escaping output fix
					}

					$fields[] = [
						'TITLE' => static::getMessage('PAYLOAD_' . $code . '_TITLE'),
						'VALUE' => $value,
					];
				}
			}

			$result->setData($fields);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	/**
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection PhpUndefinedNamespaceInspection
	 *
	 * @param string      $subject
	 * @param string      $status
	 * @param string      $semanticType [error, success, process]
	 * @param string|null $body
	 * @param string|null $type
	 *
	 * @return Delivery\Requests\Message\Message|null
	 */
	public function createMessage(
		string $subject,
		string $status,
		string $semanticType,
		string $body = null,
		string $type = null

	) : ?Delivery\Requests\Message\Message
	{
		$result = null;

		try
		{
			Assert::classExists(Delivery\Requests\Message\Message::class);
			Assert::classExists(Delivery\Requests\Message\Status::class);

			$statusObject = new Delivery\Requests\Message\Status(
				$status,
				mb_strtolower($semanticType)
			);

			$result = new Delivery\Requests\Message\Message();
			$result
				->setSubject($subject)
				->setStatus($statusObject);

			if ($body !== null)
			{
				$result->setBody($body);
			}

			if ($type !== null)
			{
				$result->setType($type);
			}
		}
		catch (Main\SystemException $exception)
		{
			//nothing
		}

		return $result;
	}

	/**
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection PhpUndefinedNamespaceInspection
	 *
	 * @param string                                 $addressee [MANAGER, RECIPIENT]
	 * @param Delivery\Requests\Message\Message|null $message
	 * @param int                                    $requestId
	 * @param int                                    $shipmentId
	 *
	 * @return void
	 */
	public function sendMessage(
		string $addressee,
		?Delivery\Requests\Message\Message $message,
		int $requestId,
		int $shipmentId
	): void
	{
		if ($message === null) { return; }
		if (!method_exists(Delivery\Requests\Manager::class, 'sendMessage')) { return; }

		Delivery\Requests\Manager::sendMessage(
			mb_strtoupper($addressee),
			$message,
			$requestId,
			$shipmentId
		);
	}
}
