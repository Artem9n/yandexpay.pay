<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupOptions\Stage;

use YandexPay\Pay\Trading\Action\Rest\Reference;
use YandexPay\Pay\Trading\Action\Rest\Stage;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class PickupOptionsCollector extends Stage\OrderDeliveryCollector
{
	/** @var array|null */
	protected $bounds;

	public function __construct(Reference\EffectiveResponse $response, string $key = '', array $bounds = null)
	{
		parent::__construct($response, $key);
		$this->bounds = $bounds;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$deliveries = $this->restrictedDeliveries($state, true);
		$deliveries = $this->filterDeliveryByType($state, $deliveries, EntitySale\Delivery::PICKUP_TYPE);
		$locationStores = $this->locationStores($state, $deliveries);
		$pickupOptions = $this->collectPickupOptions($state, $locationStores);

		$this->write($pickupOptions);
	}

	protected function locationStores(State\OrderCalculation $state, array $deliveries) : array
	{
		$locationStores = [];

		foreach ($deliveries as $deliveryId)
		{
			$allStores = $state->environment->getDelivery()->getPickupStores($deliveryId, $state->order, $this->bounds);

			foreach ($allStores as $locationId => $stores)
			{
				if (!isset($locationStores[$locationId]))
				{
					$locationStores[$locationId] = [];
				}

				$locationStores[$locationId][$deliveryId] = $stores;
			}
		}

		return $locationStores;
	}

	protected function collectPickupOptions(State\OrderCalculation $state, array $locationStores) : array
	{
		$result = [];

		foreach ($locationStores as $locationId => $locationDeliveries)
		{
			$state->order->clearCalculatable();
			$state->order->setLocation($locationId);

			$locationDeliveries = array_intersect_key(
				$locationDeliveries,
				array_flip($this->restrictedDeliveries($state))
			);

			foreach ($locationDeliveries as $deliveryId => $deliveryStores)
			{
				if (!$state->environment->getDelivery()->isCompatible($deliveryId, $state->order)) { continue; }

				foreach ($deliveryStores as $store)
				{
					$result[] = $this->collectPickupOption($deliveryId, $store, $locationId);
				}
			}
		}

		return $result;
	}

	protected function collectPickupOption(int $deliveryId, array $store, int $locationId = null) : array
	{
		return [
			'pickupPointId' => implode(':', [$deliveryId, $store['ID'], $locationId, $store['ZIP']]), //todo send need metadata
			'provider' => $store['PROVIDER'] ?? 'IN_STORE',
			'address' => $store['ADDRESS'],
			'location' =>  [
				'latitude' => (float)$store['GPS_N'],
				'longitude' => (float)$store['GPS_S'],
			],
			'title'     => $store['TITLE'],
			'description' => $store['DESCRIPTION'],
			'phones' => explode(', ', $store['PHONE']),
			'amount' => $store['AMOUNT'] ?? null,
		];
	}
}

