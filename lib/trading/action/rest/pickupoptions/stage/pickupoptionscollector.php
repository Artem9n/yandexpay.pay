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
		$result = [];

		$deliveries = $this->restrictedDeliveries($state);
		$deliveries = $this->filterDeliveryByType($state, $deliveries, EntitySale\Delivery::PICKUP_TYPE);

		foreach ($deliveries as $deliveryId)
		{
			if (!$state->environment->getDelivery()->isCompatible($deliveryId, $state->order)) { continue; }

			$allStores = $state->environment->getDelivery()->getPickupStores($deliveryId, $state->order, $this->bounds);

			foreach ($allStores as $locationId => $stores)
			{
				foreach ($stores as $store)
				{
					$result[] = $this->collectPickupOption($deliveryId, $store, $locationId);
				}
			}
		}

		$this->write($result);
	}

	protected function collectPickupOption(int $deliveryId, array $store, int $locationId = null) : array
	{
		return [
			'pickupPointId' => implode(':', [$deliveryId, $store['ID'], $locationId]), //todo send need metadata
			'provider' => 'IN_STORE',
			'address' => $store['ADDRESS'],
			'location' =>  [
				'latitude' => (float)$store['GPS_N'],
				'longitude' => (float)$store['GPS_S'],
			],
			'title'     => $store['TITLE'],
			'description' => $store['DESCRIPTION'],
			'phones' => explode(', ', $store['PHONE']),
		];
	}
}

