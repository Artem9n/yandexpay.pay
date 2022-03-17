<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderDelivery
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->fillDelivery($state);
	}

	protected function fillDelivery(State\OrderCalculation $state) : void
	{
		$delivery = $this->request->getDelivery();

		$deliveryId = $delivery->getId();
		$price = $delivery->getAmount();

		if ((string)$deliveryId === '')
		{
			$deliveryId = $state->environment->getDelivery()->getEmptyDeliveryId();
		}

		$state->order->createShipment($deliveryId);
	}
}