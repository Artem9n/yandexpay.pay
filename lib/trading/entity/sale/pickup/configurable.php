<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Sale;

/** @property Sale\Delivery\Services\Configurable $service */
class Configurable extends AbstractAdapter
{
	public function __construct(Sale\Delivery\Services\Configurable $service)
	{
		parent::__construct($service);
	}

	public function getStores(Sale\OrderBase $order) : array
	{
		$storeIds = $this->getUsedStoreIds();
		$stores = $this->loadStores($storeIds);

		if (empty($stores)) { return []; }

		$locationId = $this->getLocationId($this->service->getId());

		return $this->combineStores($stores, $locationId);
	}

	protected function loadStores(array $storeIds) : array
	{
		if (empty($storeIds)) { return []; }

		$result = [];

		$query = Catalog\StoreTable::getList([
			'filter' => [
				'=ID' => $storeIds,
				'!GPS_N' => false,
				'!GPS_S' => false,
				'!ADDRESS' => false
			]
		]);

		while ($store = $query->fetch())
		{
			$result[] = $store;
		}

		return $result;
	}

	protected function getUsedStoreIds() : array
	{
		return Sale\Delivery\ExtraServices\Manager::getStoresList($this->service->getId());
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