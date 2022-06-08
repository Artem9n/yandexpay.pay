<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property Sale\Delivery\Services\Configurable $service */
class Configurable extends AbstractAdapter
{
	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}

	public function markSelected(Sale\OrderBase $order, array $store = []) : void
	{
		// TODO: Implement markSelected() method.
	}

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\Configurable)) { return false; }

		$stores = Sale\Delivery\ExtraServices\Manager::getStoresList($service->getId());

		return !empty($stores);
	}

	public function getStores(Sale\OrderBase $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		$storeIds = $this->getUsedStoreIds($service);
		$stores = $this->loadStores($storeIds, $bounds);

		if (empty($stores)) { return []; }

		$locationId = $this->getLocationId($service->getId());

		return $this->combineStores($stores, $locationId);
	}

	protected function loadStores(array $storeIds, array $bounds = null) : array
	{
		if (empty($storeIds)) { return []; }

		$filter = [
			'=ID' => $storeIds,
			'!GPS_N' => false,
			'!GPS_S' => false,
			'!ADDRESS' => false,
		];

		if ($bounds !== null)
		{
			$filter += [
				['<=GPS_N' => $bounds['ne']['latitude']],
				['>=GPS_N' => $bounds['sw']['latitude']],
				['<=GPS_S' => $bounds['ne']['longitude']],
				['>=GPS_S' => $bounds['sw']['longitude']]
			];
		}

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

	protected function combineStores(array $stores, int $locationId) : array
	{
		if ($locationId <= 0) { return $stores; }

		return [$locationId => $stores];
	}

	public function getLocationId(int $deliveryId) : ?string
	{
		$locationRestrict = $this->getLocationByRestrict($deliveryId);
		$locationSettings = $this->getLocationBySettingSale();
		$locationCode = $locationRestrict ?? $locationSettings;

		return \CSaleLocation::getLocationIDbyCODE($locationCode);
	}

	public function getLocationByRestrict(int $deliveryId) : ?string
	{
		$result = Sale\Delivery\DeliveryLocationTable::getList([
			'filter' => [
				'=DELIVERY_ID' => $deliveryId
			]
		])->fetchCollection();

		$result = $result->getLocationCodeList();

		if (empty($result)) { return  null; }

		return end($result);
	}

	public function getLocationBySettingSale() : ?string
	{
		return Main\Config\Option::get('sale', 'location', null);
	}
}