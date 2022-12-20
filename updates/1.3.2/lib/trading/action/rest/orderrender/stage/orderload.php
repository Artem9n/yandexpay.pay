<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use Bitrix\Main;
use Bitrix\Sale;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderLoad
{
	protected $orderId;
	protected $merchantId;

	public function __construct(string $orderId)
	{
		$this->orderId = $orderId;
	}

	public function __invoke(State\Order $state)
	{
		$this->load($state);
		$this->resolvePayment($state);
		$this->resolveBasket($state);
		$this->resolveDelivery($state);
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
			throw new DtoProperty('order not found', 'ORDER_NOT_FOUND');
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
			throw new DtoProperty('payment not found', 'OTHER');
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
}

