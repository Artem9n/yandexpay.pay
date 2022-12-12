<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupOptions\Stage;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Action\Rest\Reference;
use YandexPay\Pay\Trading\Action\Rest\Stage;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class PickupOptionsCollector extends Stage\OrderDeliveryCollector
{
	/** @var array|null */
	protected $bounds;
	/** @var array<int, int[]]>*/
	protected $locationRestricted = [];

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
			$allStores = $state->environment->getDelivery()->getPickupStores($deliveryId, $state->order, $this->bounds);

			foreach ($allStores as $locationId => $stores)
			{
				if (!$this->isRestricted($state, $locationId, $deliveryId)) { continue; }

				foreach ($stores as $store)
				{
					$result[] = $this->collectPickupOption($deliveryId, $store, $locationId);
				}
			}
		}

		$this->write($result);
	}

	protected function isRestricted(State\OrderCalculation $state, int $locationId, int $deliveryId) : bool
	{
		$deliveries = $this->locationRestrictedDeliveries($state, $locationId);
		$deliveries = array_flip($deliveries);

		return isset($deliveries[$deliveryId]);
	}

	protected function locationRestrictedDeliveries(State\OrderCalculation $state, int $locationId) : array
	{
		if (!isset($this->locationRestricted[$locationId]))
		{
			$this->locationRestricted[$locationId] = $this->calculateLocationRestricted($state, $locationId);
		}

		return $this->locationRestricted[$locationId];
	}

	protected function calculateLocationRestricted(State\OrderCalculation $state, int $locationId) : array
	{
		$previousLocation = $state->order->getLocation();

		$state->order->clearCalculatable();
		$state->order->setLocation($locationId);

		$deliveries = $this->restrictedDeliveries($state);

		$state->order->clearCalculatable();
		$state->order->setLocation($previousLocation);

		return $deliveries;
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
		];
	}
}

