<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

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
		$state->orderAdapter = $state->environment->getOrderRegistry()->load($this->orderId);
		$state->order = $state->orderAdapter->getOrder();
	}

	protected function resolvePayment(State\Order $state) : void
	{
		$state->payment = $state->orderAdapter->getPayment();
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