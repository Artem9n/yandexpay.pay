<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Site;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Catalog;
use Bitrix\Sale\Shipment;
use YandexPay\Pay\Config;
use YandexPay\Pay\Data;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;

/** @property Sale\Delivery\Services\Configurable $service */
class Store extends AbstractAdapter
{
	protected $locationsRestrict;

	public function load() : bool
	{
		return Main\Loader::includeModule('catalog');
	}

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\Configurable)) { return false; }

		$stores = Sale\Delivery\ExtraServices\Manager::getStoresList($service->getId());

		return !empty($stores);
	}

	public function getStores(Sale\Order $order, Sale\Delivery\Services\Base $service, array $bounds) : array
	{
		$storeIds = $this->getUsedStoreIds($service);
		$stores = $this->loadStores($storeIds, $bounds);

		$strategyType = Config::getOption('stores_by_available');

		if ((string)$strategyType !== '')
		{
			$strategyAvailable = EntitySale\Delivery\Site\AvailableStore\Strategy::getInstance($strategyType);
			$stores = $strategyAvailable->resolve($stores, $order);
		}

		if (empty($stores)) { return []; }

		$filterStoresByLocations = $this->filterStoresByLocations($stores);

		if (!empty($filterStoresByLocations)) { return $filterStoresByLocations; }

		$locationId = $this->getLocationId($service->getId());

		return [$locationId => $stores];
	}

	protected function loadStores(array $storeIds, array $bounds) : array
	{
		if (empty($storeIds)) { return []; }

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
			$result[$store['ID']] = $store;
		}

		return $result;
	}

	protected function getUsedStoreIds(Sale\Delivery\Services\Base $service) : array
	{
		return Sale\Delivery\ExtraServices\Manager::getStoresList($service->getId());
	}

	protected function filterStoresByLocations(array $stores) : array
	{
		$result = [];

		$metadata = new Data\Location\MetaData();
		$finder = new Data\Location\Bounds($metadata);
		$storesByLocation = [];

		foreach ($stores as $store)
		{
			$locationCode = $finder->findClosestCity($store['GPS_N'], $store['GPS_S']);

			if ($locationCode === null) { continue; }

			if (!isset($storesByLocation[$locationCode])) { $storesByLocation[$locationCode] = []; }

			$storesByLocation[$locationCode][] = $store;
		}

		if (empty($storesByLocation)) { return $result; }

		$locationsIdsMap = $this->getLocationIdsByCodes(array_keys($storesByLocation));

		foreach ($storesByLocation as $locationsCode => $locationStores)
		{
			$locationsId = $locationsIdsMap[$locationsCode];

			if (!isset($locationsId)) { continue; }

			$result[$locationsId] = $locationStores;
		}

		return $result;
	}

	public function getLocationId(int $deliveryId) : int
	{
		$locationRestricts = $this->getLocationsByRestrict($deliveryId);
		$locationRestrict = !empty($locationRestricts) ? end($locationRestricts) : null;
		$locationSettings = $this->getLocationBySettingSale();
		$locationCode = $locationRestrict ?? $locationSettings;

		return (int)\CSaleLocation::getLocationIDbyCODE($locationCode);
	}

	public function getLocationsByRestrict(int $deliveryId) : array
	{
		if ($this->locationsRestrict === null)
		{
			$this->locationsRestrict = $this->loadLocationsRestrict($deliveryId);
		}

		return $this->locationsRestrict;
	}

	protected function loadLocationsRestrict(int $deliveryId)
	{
		$result = Sale\Delivery\DeliveryLocationTable::getList([
			'filter' => [
				'=DELIVERY_ID' => $deliveryId,
			],
		])->fetchCollection();

		return $result->getLocationCodeList();
	}

	public function getLocationBySettingSale() : ?string
	{
		return Main\Config\Option::get('sale', 'location', null);
	}

	public function markSelectedPickup(Sale\Order $order, string $storeId, string $address) : void
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

	public function serviceType() : string
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
			'limit' => 1,
		]);

		if ($store = $query->fetch())
		{
			$store['DESCRIPTION'] = $this->pickupStoreDescription($store);
			$result = $store;
		}

		return $result;
	}

	protected function pickupStoreDescription(array $store) :string
	{
		$template = Config::getOption('catalog_store_description', '#DESCRIPTION#');

		if (trim($template) === '') {	return ''; }

		foreach ($store as $code => $value)
		{
			if (!is_scalar($value)) { continue; }

			$marker = sprintf("#%s#", $code);

			$template = str_replace($marker, $value, $template);
		}

		return $template;
	}

	public function providerType() : ?string
	{
		return 'IN_STORE';
	}

	protected function addressCode(Sale\Order $order) : string
	{
		return '';
	}

	protected function zipCode(Sale\Order $order) : string
	{
		return '';
	}
}