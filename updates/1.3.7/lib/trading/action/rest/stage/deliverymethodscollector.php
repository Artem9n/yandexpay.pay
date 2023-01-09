<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class DeliveryMethodsCollector extends OrderDeliveryCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$deliveries = $this->restrictedDeliveries($state, true);
		$pickupDeliveries = $this->filterDeliveryByType($state, $deliveries, EntitySale\Delivery::PICKUP_TYPE);
		$courierDeliveries = $this->filterDeliveryByType($state, $deliveries, EntitySale\Delivery::DELIVERY_TYPE);

		$result = [];

		if (!empty($courierDeliveries))
		{
			$result[] = 'COURIER';
		}

		if (!empty($pickupDeliveries))
		{
			$result[] = 'PICKUP';
		}

		$this->write($result);
	}
}