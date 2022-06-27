<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderPickup
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$state->order->setLocation($this->request->getPickup()->getLocationId());
		$this->fillPickup($state);
	}

	protected function fillPickup(State\OrderCalculation $state) : void
	{
		$deliveryId = $this->request->getPickup()->getPickupId();
		$price = $this->request->getPickup()->getAmount();

		if ((string)$deliveryId === '')
		{
			$deliveryId = $state->environment->getDelivery()->getEmptyDeliveryId();
		}

		$state->order->createShipment($deliveryId, $price);
	}
}