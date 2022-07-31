<?php

namespace YandexPay\Pay\Delivery\Yandex;

use Bitrix\Main\SystemException;
use Bitrix\Sale;
use Bitrix\Main\Error;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Delivery\Requests\HandlerBase;
use Bitrix\Sale\Delivery\Requests\Result;
use Bitrix\Sale\Shipment;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay\Delivery\Yandex\Api;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Entity\Registry;

/**
 * Class RequestHandler
 * @package \YandexPay\Pay\Delivery\Yandex\Handler
 */
class RequestHandler extends HandlerBase
{
	/** @var \YandexPay\Pay\Trading\Entity\Reference\Environment */
	protected $environment;

	public const CANCEL_ACTION_CODE = 'cancel';
	public const RENEW_ACTION_CODE = 'renew';
	public const CANCEL_INFO_ACTION_CODE = 'cancelInfo';
	public const ACCEPT_ACTION_CODE = 'accept';
	public const CREATE_ACTION_CODE = 'create';

	public function __construct(Delivery\Services\Base $deliveryService)
	{
		$this->environment = Registry::getEnvironment();
		parent::__construct($deliveryService);
	}

	/**
	 * @inheritDoc
	 */
	public function create(array $shipmentIds, array $additional = array()) : Result
	{
		$result = new Result();
		$requestResult = new Delivery\Requests\RequestResult();

		try
		{
			$shipmentId = (int)$shipmentIds[0];
			$orderId = $this->getOrderIdByShipmentId($shipmentId);

			if ($orderId === null)
			{
				throw new SystemException('not found order id');
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
		catch (SystemException $exception)
		{
			$result->addError(new Error($exception->getMessage()));
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
			throw new SystemException('order not found');
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
			throw new SystemException('not paySystemId');
		}

		/** @var \Sale\Handlers\PaySystem\YandexPayHandler $handler */
		$handler = $this->environment->getPaySystem()->getHandler($paymentOrder->getPaymentSystemId());

		Assert::typeOf($handler, YandexPayHandler::class, 'not YandexPayHandler');

		$gateway = $handler->wakeUpGateway($paymentOrder);

		$isTestMode = $gateway->isTestHandlerMode();
		$apiKey = $isTestMode ?
			$gateway->getParameter('YANDEX_PAY_MERCHANT_ID', true)
			: $gateway->getParameter('YANDEX_PAY_REST_API_KEY', true);

		return [$isTestMode, $apiKey];
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
			self::CANCEL_ACTION_CODE => $this->getCancelActionName(),
			self::RENEW_ACTION_CODE => $this->getUpdateActionName(),
			self::CANCEL_INFO_ACTION_CODE => $this->getInfoActionName(),
			self::ACCEPT_ACTION_CODE => $this->getAcceptActionName(),
		];
	}

	public function getCancelActionName() : string
	{
		return 'cancel';
	}

	public function getUpdateActionName() : string
	{
		return 'renew';
	}

	public function getInfoActionName() : string
	{
		return 'cancel-info';
	}

	public function getAcceptActionName() : string
	{
		return 'accept';
	}

	/**
	 * @inheritDoc
	 */
	public function executeAction($requestId, $actionType, array $additional) : Result
	{
		$result = new Result();

		try
		{
			$actions = $this->getActions($requestId);

			if (!isset($actions[$actionType]))
			{
				throw new SystemException('not action');
			}

			$request = Sale\Delivery\Requests\RequestTable::getById($requestId)->fetch();

			if (!$request)
			{
				throw new SystemException('not found transport request');
			}

			$orderId = $request['EXTERNAL_ID'];
			[$isTestMode, $apiKey] = $this->getDataForRequest($orderId);

			if ($actionType === static::ACCEPT_ACTION_CODE)
			{
				$result = $this->acceptRequest($orderId, $isTestMode, $apiKey);
			}
			elseif ($actionType === static::RENEW_ACTION_CODE)
			{
				$result = $this->renewRequest($orderId, $isTestMode, $apiKey);
			}
		}
		catch (SystemException $exception)
		{
			$result->addError(new Error($exception->getMessage()));
		}

		return $result;
	}

	protected function createRequest(int $orderId, bool $isTestMode, string $apiKey) : Result
	{
		$result = new Result();

		try
		{
			$request = new Api\Create\Request();
			$request->setTestMode($isTestMode);
			$request->setApiKey($apiKey);
			$request->setOrderId($orderId);
			$request->buildResponse($request->send(), Api\Create\Response::class);
		}
		catch (SystemException $exception)
		{
			$result->addError(new Error($exception->getMessage()));
		}

		return $result;
	}

	protected function acceptRequest(int $orderId, bool $isTestMode, string $apiKey) : Result
	{
		$result = new Result();

		try
		{
			$request = new Api\Accept\Request();
			$request->setTestMode($isTestMode);
			$request->setApiKey($apiKey);
			$request->setOrderId($orderId);
			$responce = $request->buildResponse($request->send(), Api\Accept\Response::class);
		}
		catch (SystemException $exception)
		{
			$result->addError(new Error($exception->getMessage()));
		}

		return $result;
	}

	protected function renewRequest(int $orderId, bool $isTestMode, string $apiKey) : Result
	{
		$result = new Result();

		try
		{
			$request = new Api\Renew\Request();
			$request->setTestMode($isTestMode);
			$request->setApiKey($apiKey);
			$request->setOrderId($orderId);
			$responce = $request->buildResponse($request->send(), Api\Renew\Response::class);
		}
		catch (SystemException $exception)
		{
			$result->addError(new Error($exception->getMessage()));
		}

		return $result;
	}

	/**
	 * @param $requestId
	 * @return Result
	 */
	public function cancelRequest($requestId): Result
	{
		$result = new Result();

		return $result;
	}

	public function getFormFields($formFieldsType, array $shipmentIds, array $additional = array()) : array
	{
		return [];
	}



	/**
	 * @inheritDoc
	 */
	public function delete($requestId)
	{
		return new Result();
	}

	/**
	 * @inheritDoc
	 */
	public function hasCallbackTrackingSupport(): bool
	{
		return true;
	}

	public function getContent($requestId) : Result
	{
		$result = new Result();

		return $result;
	}
}
