<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Site;

use Bitrix\Catalog;
use Bitrix\Sale;
use Bitrix\Sale\Shipment;
use YandexPay\Pay\Data;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;

/** @property Sale\Delivery\Services\Configurable $service */
class Store extends AbstractAdapter
{
	public function load() : bool
	{
		return true;
	}

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\Configurable)) { return false; }

		$stores = Sale\Delivery\ExtraServices\Manager::getStoresList($service->getId());

		return !empty($stores);
	}

	public function getStores(Sale\Order $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		$storeIds = $this->getUsedStoreIds($service);
		$stores = $this->loadStores($storeIds, $bounds);

		if (empty($stores)) { return []; }

		return $this->filterStoresByLocations($stores, $bounds);
	}

	protected function loadStores(array $storeIds, array $bounds = null) : array
	{
		if (empty($storeIds) || $bounds === null) { return []; }

		$filter = [
			'=ID' => $storeIds,
			['<=GPS_N' => $bounds['ne']['latitude']],
			['>=GPS_N' => $bounds['sw']['latitude']],
			['<=GPS_S' => $bounds['ne']['longitude']],
			['>=GPS_S' => $bounds['sw']['longitude']],
			'!ADDRESS' => false,
		];

		$result = [];

		$query = Catalog\StoreTable::getList([
			'filter' => $filter
		]);

		while ($store = $query->fetch())
		{
			$result[] = $store;
		}

		return $result;
	}

	protected function getUsedStoreIds(Sale\Delivery\Services\Base $service) : array
	{
		return Sale\Delivery\ExtraServices\Manager::getStoresList($service->getId());
	}

	protected function getLocationIdsByCodes(array $locationsCodes) : array
	{
		$result = [];

		$query = Sale\Location\LocationTable::getList(array(
			'select' => [ 'ID', 'CODE' ],
			'filter' => [ '=CODE' => $locationsCodes ],
		));

		while ($row = $query->fetch())
		{
			$result[$row['CODE']] = $row['ID'];
		}

		return $result;
	}

	protected function filterStoresByLocations(array $stores, array $bounds) : array
	{
		$result = [];

		$metadata = new Data\Location\MetaData();
		$finder = new Data\Location\Bounds($metadata);

		$locations = $finder->search(
			$bounds['sw']['latitude'],
			$bounds['sw']['longitude'],
			$bounds['ne']['latitude'],
			$bounds['ne']['longitude']);

		if (empty($locations)) { return $result; }

		$locationsIdsMap = $this->getLocationIdsByCodes(array_keys($locations));

		foreach ($stores as $store)
		{
			$locationCode = $finder->findClosestCity($locations, $store['GPS_N'], $store['GPS_S']);

			if ($locationCode === null) { continue; }

			$locationId = $locationsIdsMap[$locationCode];

			if ($locationId === null) { continue; }

			if (!isset($result[$locationId])) { $result[$locationId] = []; }

			$result[$locationId][] = $store;
		}

		return $result;
	}

	public function markSelected(Sale\Order $order, string $storeId = null, string $address = null) : void
	{
		$shipments = $order->getShipmentCollection();

		/** @var Shipment $shipment */
		foreach ($shipments as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$shipment->setStoreId($storeId);
				break;
			}
		}
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}

	public function getDetailPickup(string $storeId) : array
	{
		$result = [];

		$query = Catalog\StoreTable::getList([
			'filter' => [
				'=ID' => $storeId,
			],
			'limit' => 1
		]);

		if ($store = $query->fetch())
		{
			$result = $store;
		}

		return $result;
	}
}