<?php

namespace YandexPay\Pay\Delivery\Yandex;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Shipment;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay\Delivery\Yandex\Api;
use YandexPay\Pay\Delivery\Yandex\Internals\RepositoryTable;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Registry;

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

	public function __construct(Delivery\Services\Base $deliveryService)
	{
		$this->environment = Registry::getEnvironment();
		parent::__construct($deliveryService);
	}

	/**
	 * @inheritDoc
	 */
	public function create(array $shipmentIds, array $additional = array()) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();
		$requestResult = new Delivery\Requests\RequestResult();

		try
		{
			$shipmentId = (int)$shipmentIds[0];
			$orderId = $this->getOrderIdByShipmentId($shipmentId);

			if ($orderId === null)
			{
				throw new Main\SystemException('not found order id');
			}

			[$isTestMode, $apiKey] = $this->getDataForRequest($orderId);

			$result = $this->createRequest($orderId, $isTestMode, $apiKey);

			if ($result->isSuccess())
			{
				$requestResult->setExternalId($orderId);
				$shipmentResult = new Delivery\Requests\ShipmentResult($shipmentId);
				$requestResult->addResult($shipmentResult);
				$result->addResult($requestResult);
			}
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
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

		$paymentOrder = null;
		/** @var \Bitrix\Sale\Payment $payment */
		foreach ($order->getPaymentCollection() as $payment)
		{
			if ($payment->isInner()) { continue; }
			$paymentOrder = $payment;
		}

		if ($paymentOrder === null)
		{
			throw new Main\SystemException('not payment');
		}

		/** @var \Sale\Handlers\PaySystem\YandexPayHandler $handler */
		$handler = $this->environment->getPaySystem()->getHandler($paymentOrder->getPaymentSystemId());

		Assert::typeOf($handler, YandexPayHandler::class, 'not YandexPayHandler');

		return [$handler->isTestMode($paymentOrder), $handler->getApiKey($paymentOrder)];
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

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getActions($requestId) : array
	{
		return [
			self::CANCEL_ACTION_CODE => static::getMessage('ACTION_CANCEL'),
			self::RENEW_ACTION_CODE => static::getMessage('ACTION_RENEW'),
			self::ACCEPT_ACTION_CODE => static::getMessage('ACTION_ACCEPT'),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function executeAction($requestId, $actionType, array $additional) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		try
		{
			$actions = $this->getActions($requestId);

			if (!isset($actions[$actionType]))
			{
				throw new Main\SystemException('not action');
			}

			$request = Sale\Delivery\Requests\RequestTable::getById($requestId)->fetch();

			if (!$request)
			{
				throw new Main\SystemException('not found transport request');
			}

			$orderId = $request['EXTERNAL_ID'];
			[$isTestMode, $apiKey] = $this->getDataForRequest($orderId);
			$transportStatus = $this->getTrasportStatus($requestId);

			if ($actionType === static::ACCEPT_ACTION_CODE)
			{
				if ($transportStatus !== static::READY_FOR_APPROVAL_STATUS)
				{
					throw new Main\SystemException(static::getMessage('ACCEPT_UNAVAILABLE'));
				}

				$result = $this->acceptAction($orderId, $isTestMode, $apiKey);
			}
			elseif ($actionType === static::RENEW_ACTION_CODE)
			{
				if ($transportStatus !== static::EXPIRED_STATUS)
				{
					throw new Main\SystemException(static::getMessage('RENEW_UNAVAILABLE'));
				}

				$result = $this->renewAction($orderId, $isTestMode, $apiKey);
			}
			elseif ($actionType === static::CANCEL_ACTION_CODE)
			{
				$state = $additional['CANCEL_STATE'];

				if ($state === static::UNAVAILABLE_STATE_CANCEL)
				{
					throw new Main\SystemException(static::getMessage('CANCEL_UNAVAILABLE'));
				}

				$result = $this->cancelAction($orderId, $isTestMode, $apiKey, $additional['CANCEL_STATE']);
			}
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	protected function getTrasportStatus(int $requestId) : ?string
	{
		$transport = $this->getTransport($requestId, ['STATUS']);
		return $transport['STATUS'] ?? null;
	}

	protected function getTransport(int $requestId, array $selectFields = []) : array
	{
		$result = [];

		$select = ['*'];

		if (!empty($selectFields))
		{
			$select = $selectFields;
		}

		$query = RepositoryTable::getList([
			'filter' => [
				'=REQUEST_ID' => $requestId
			],
			'select' => $select,
			'limit' => 1,
		]);

		if ($transport = $query->fetch())
		{
			$result = $transport;
		}

		return $result;
	}

	protected function createRequest(int $orderId, bool $isTestMode, string $apiKey) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		try
		{
			$request = new Api\Create\Request();
			$request->setTestMode($isTestMode);
			$request->setApiKey($apiKey);
			$request->setOrderId($orderId);
			$request->buildResponse($request->send(), Api\Create\Response::class);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	protected function acceptAction(int $orderId, bool $isTestMode, string $apiKey) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		try
		{
			$request = new Api\Accept\Request();
			$request->setTestMode($isTestMode);
			$request->setApiKey($apiKey);
			$request->setOrderId($orderId);
			$request->buildResponse($request->send(), Api\Accept\Response::class);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	protected function renewAction(int $orderId, bool $isTestMode, string $apiKey) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		try
		{
			$request = new Api\Renew\Request();
			$request->setTestMode($isTestMode);
			$request->setApiKey($apiKey);
			$request->setOrderId($orderId);
			$request->buildResponse($request->send(), Api\Renew\Response::class);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	protected function cancelInfoAction(int $orderId, bool $isTestMode, string $apiKey) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		try
		{
			$request = new Api\CancelInfo\Request();
			$request->setTestMode($isTestMode);
			$request->setApiKey($apiKey);
			$request->setOrderId($orderId);
			$responce = $request->buildResponse($request->send(), Api\CancelInfo\Response::class);

			$result->setData([
				'CANCEL_STATE' => $responce->getCancelState(),
			]);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	protected function cancelAction(int $orderId, bool $isTestMode, string $apiKey, string $state) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();

		try
		{
			$request = new Api\Cancel\Request();
			$request->setTestMode($isTestMode);
			$request->setApiKey($apiKey);
			$request->setOrderId($orderId);
			$request->setCancelState($state);
			$request->buildResponse($request->send(), Api\Cancel\Response::class);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
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

			[$isTestMode, $apiKey] = $this->getDataForRequest($orderId);

			$cancelResult = $this->cancelInfoAction($orderId, $isTestMode, $apiKey);

			if (!$cancelResult->isSuccess()) { return $result; }
			$data = $cancelResult->getData();

			$result = [
				'CANCEL_STATE' => [
					'TYPE' => 'ENUM',
					'TITLE' => self::getMessage('CANCEL_INFO_TITLE'),
					'VALUE' => $data['CANCEL_STATE'],
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

	/**
	 * @inheritDoc
	 */
	public function delete($requestId) : Delivery\Requests\Result
	{
		$result = new Delivery\Requests\Result();
		$transport = $this->getTransport($requestId);

		if (empty($transport)) { return $result; }

		$deleteResult = RepositoryTable::delete($transport['ID']);

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
		$fields = [];

		$transport = $this->getTransport($requestId, ['STATUS', 'TIMESTAMP_X']);

		if (!empty($transport))
		{
			foreach ($transport as $code => $value)
			{
				if (empty($value)) { continue; }

				$value = $code === 'STATUS' ? static::getMessage('STATUS_' . $value) : $value;

				$fields[] = [
					'TITLE' => static::getMessage($code . '_TITLE'),
					'VALUE' => $value,
				];
			}
		}

		if (empty($fields))
		{
			$fields[] = [
				'TITLE' => static::getMessage('STATUS_TITLE'),
				'VALUE' => static::getMessage('STATUS_DEFAULT'),
			];
		}

		$result->setData($fields);

		return $result;
	}
}
