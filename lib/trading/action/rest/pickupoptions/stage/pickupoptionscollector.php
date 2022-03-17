<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupOptions\Stage;

use YandexPay\Pay\Trading\Action\Rest\Stage;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class PickupOptionsCollector extends Stage\OrderDeliveryCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$result = [];

		$deliveries = $this->restrictedDeliveries($state);
		$deliveries = $this->filterDeliveryByType($state, $deliveries, EntitySale\Delivery::PICKUP_TYPE);

		foreach ($deliveries as $deliveryId)
		{
			if (!$state->environment->getDelivery()->isCompatible($deliveryId, $state->order)) { continue; }

			$allStores = $state->environment->getDelivery()->getPickupStores($deliveryId, $state->order);
			//$storesByLocation = $this->groupStoresByLocation($allStores); //todo, need group?
			foreach ($allStores as $locationId => $stores)
			{
				$state->order->setLocation($locationId);

				$calculationResult = $state->environment->getDelivery()->calculate($deliveryId, $state->order);

				if (!$calculationResult->isSuccess()) { continue; }

				foreach ($stores as $store)
				{
					$result[] = $this->collectPickupOption($calculationResult, $store, $locationId);
				}
			}
		}

		$this->write($result);
	}

	protected function collectPickupOption(EntityReference\Delivery\CalculationResult $calculationResult, array $store, int $locationId = null) : array
	{
		return [
			'pickupPointId' => implode(':', [$calculationResult->getDeliveryId(), $store['ID'], $locationId]), //todo send need metadata
			'provider' => ['IN_STORE'], //todo enum<PICKPOINT|...|IN_STORE>
			'address' => $store['ADDRESS'],
			'location' =>  [
				'latitude' => (float)$store['GPS_N'],
				'longitude' => (float)$store['GPS_S'],
			],
			'title'     => $store['TITLE'],
			'fromDate' => $calculationResult->getDateFrom()->format('Y-m-d'),
			'toDate' => $calculationResult->getDateTo()->format('Y-m-d'),
			'amount'    => (float)$calculationResult->getPrice(),
			'description' => $store['DESCRIPTION'],
			'phones' => explode(', ', $store['PHONE']),
			'schedule' =>  [ // Опционально. График работы точки.
				'label' => '', //todo # Например, "пн-пт"
				'fromTime' => '', //todo # HH:mm, "08:00"
				'toTime' => '', //todo # HH:mm, "20:00"
			],
			'storagePeriod' => 0, // todo # Опционально. Срок хранения товара в точке самовывоза в днях.
		];
	}
}

