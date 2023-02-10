<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Site;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Sale;
use Bitrix\Sale\Shipment;
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

		$locationId = $this->getLocationId($service->getId());

		return $this->combineStores($stores, $locationId);
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

	protected function combineStores(array $stores, int $locationId) : array
	{
		return [$locationId => $stores];
	}

	public function getLocationId(int $deliveryId) : int
	{
		$locationRestrict = $this->getLocationByRestrict($deliveryId);
		$locationSettings = $this->getLocationBySettingSale();
		$locationCode = $locationRestrict ?? $locationSettings;

		return (int)\CSaleLocation::getLocationIDbyCODE($locationCode);
	}

	public function getLocationByRestrict(int $deliveryId) : ?string
	{
		$result = Sale\Delivery\DeliveryLocationTable::getList([
			'filter' => [
				'=DELIVERY_ID' => $deliveryId,
			]
		])->fetchCollection();

		$result = $result->getLocationCodeList();

		if (empty($result)) { return null; }

		return end($result);
	}

	public function getLocationBySettingSale() : ?string
	{
		return Main\Config\Option::get('sale', 'location', null);
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
			$store = $this->pickupStoreDescription($store);
			$result = $store;
		}

		return $result;
	}

	protected function pickupStoreDescription(array $store) :array
	{
		$template = \YandexPay\Pay\Config::getOption('catalog_store_description');

		if (trim($template === ''))
		{
			$store['DESCRIPTION'] = '';
			return $store;
		}

		preg_match_all('/(?<=[#])[A-Z][^#]+/', $template, $matches);

		foreach ($matches[0] as $match)
		{
			if (!isset($store[$match])) { continue; }

			$template = str_replace('#' . $match . '#', $store[$match], $template);
		}

		$store['DESCRIPTION'] = $template;

		return $store;
	}
}