<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Action\Rest\Utils;

class OrderPickup
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$locationId = $this->request->getPickup()->getLocationId();

		$state->order->setLocation($locationId);

		$locationService = $state->environment->getLocation();
		$meaningfulValues = $locationService->getMeaningfulValues($locationId);

		if (!empty($meaningfulValues))
		{
			Utils\OrderProperties::setMeaningfulPropertyValues($state, $meaningfulValues);
		}

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

		$state->order->setShipments($deliveryId, $price);
	}
}