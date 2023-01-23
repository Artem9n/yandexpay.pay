<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Logger;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action as TradingAction;
use YandexPay\Pay\Trading\Action\Rest\State;
use Sale\Handlers\PaySystem\YandexPayHandler;

class OrderLoad
{
	protected $orderId;
	protected $merchantId;

	public function __construct(string $orderId, string $merchantId)
	{
		$this->orderId = $orderId;
		$this->merchantId = $merchantId;
	}

	public function __invoke(State\Order $state)
	{
		$this->load($state);
		$this->resolvePayment($state);
		$this->resolveHandler($state);
		$this->resolveBasket($state);
		$this->resolveDelivery($state);
		$this->validateMerchant($state);
		$this->bootLogLevel($state);
	}

	protected function load(State\Order $state) : void
	{
		if (!Main\Loader::includeModule('sale')) { return; }

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var \Bitrix\Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$state->order = $orderClassName::load($this->orderId);

		if ($state->order === null)
		{
			throw new TradingAction\Reference\Exceptions\DtoProperty('order not found', 'ORDER_NOT_FOUND');
		}
	}

	protected function resolvePayment(State\Order $state) : void
	{
		/** @var \Bitrix\Sale\Payment $payment */
		foreach ($state->order->getPaymentCollection() as $payment)
		{
			if (!$payment->isInner())
			{
				$state->payment = $payment;
				break;
			}
		}

		if ($state->payment === null)
		{
			throw new TradingAction\Reference\Exceptions\DtoProperty('payment not found');
		}
	}

	protected function resolveBasket(State\Order $state) : void
	{
		$state->basket = $state->order->getBasket();
	}

	protected function resolveDelivery(State\Order $state) : void
	{
		$shipmentCollection = $state->order->getShipmentCollection();

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$state->delivery = $shipment->getDelivery();
				break;
			}
		}
	}

	protected function resolveHandler(State\Order $state) : void
	{
		$state->handler = $state->environment->getPaySystem()->getHandler($state->payment->getPaymentSystemId());

		Assert::typeOf($state->handler, YandexPayHandler::class, 'not YandexPayHandler');
	}

	protected function validateMerchant(State\Order $state) : void
	{
		$merchantId = $state->handler->getParamValue($state->payment, 'MERCHANT_ID');

		if ($merchantId === null)
		{
			throw new TradingAction\Rest\Exceptions\RequestAuthentication('not setting payment merchantId');
		}

		if ($this->merchantId !== $merchantId)
		{
			throw new TradingAction\Rest\Exceptions\RequestAuthentication('Invalid merchantId');
		}
	}

	protected function bootLogLevel(State\Order $state) : void
	{
		if ($state->logger instanceof Logger\Logger)
		{
			$state->logger->setLevel($state->handler->logLevel($state->payment));
		}
	}
}