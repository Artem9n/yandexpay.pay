<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Boxberry;

use Bitrix\Sale;
use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Base extends AbstractAdapter
{
	protected $title;
	protected static $pickupList;

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		return false;
	}

	public function load() : bool
	{
		return Main\Loader::includeModule('up.boxberrydelivery');
	}

	protected function getType() : string
	{
		return '';
	}

	public function getStores(Sale\OrderBase $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		$stores = $this->loadStores($bounds);

		if (empty($stores)) { return []; }

		return $this->combineStores($stores);
	}

	protected function combineStores(array $stores) : array
	{
		$result = [];

		$locationsIds = $this->getLocationIds(array_keys($stores));

		foreach ($stores as $cityName => $pickupList)
		{
			if (!isset($locationsIds[$cityName])) { continue; }

			$result[$locationsIds[$cityName]] = $pickupList;
		}

		return $result;
	}

	protected function getLocationIds(array $locationsName) : array
	{
		$result = [];

		$query = Sale\Location\Name\LocationTable::getList([
			'filter' => [
				'NAME' => $locationsName,
				'=LANGUAGE_ID' => 'ru'
			],
			'select' => [
				'LOCATION_ID',
				'NAME'
			],
		]);

		while ($location = $query->fetch())
		{
			$result[$location['NAME']] = $location['LOCATION_ID'];
		}

		return $result;
	}

	protected function loadStores(array $bounds = null) : array
	{
		$result = [];

		\CBoxberry::initApi();

		$pickupList = \CBoxberry::methodExec('ListPoints', 36000, ['prepaid=1']);

		if (empty($pickupList) || !empty(static::$pickupList)) { return $result; }

		foreach ($pickupList as $point)
		{
			$pointGps = explode(',', $point['GPS']);

			if ($pointGps[0] <= $bounds['ne']['latitude']
				&& $pointGps[0] >= $bounds['sw']['latitude']
				&& $pointGps[1] <= $bounds['ne']['longitude']
				&& $pointGps[1] >= $bounds['sw']['longitude'])
			{
				$result[$point['CityName']][] = [
					'ID' => $point['Code'],
					'ADDRESS' => $point['Address'] ?: $point['AddressReduce'],
					'TITLE' => sprintf('%s (%s) ', 'Boxberry', $this->title),
					'GPS_N' => $pointGps[0],
					'GPS_S' => $pointGps[1],
					'SCHEDULE' => $point['WorkSchedule'],
					'PHONE' => $point['Phone'],
					'DESCRIPTION' => $point['TripDescription'],
					'PROVIDER' => 'Boxberry',
				];
			}
		}

		static::$pickupList = $result;

		return $result;
	}

	public function getDetailPickup(string $storeId) : array
	{
		\CBoxberry::initApi();

		$point = \CBoxberry::methodExec('PointsDescription', 3600, ['code=' . $storeId]);

		if (isset($point['err']) || !$point)
		{
			throw new DtoProperty(($point['err'] ?? 'not detail point: ') . $storeId, 'OTHER');
		}

		$pointGps = explode(',', $point['GPS']);

		return [
			'ID' => $storeId,
			'ADDRESS' => $point['Address'] ?: $point['AddressReduce'],
			'TITLE' => sprintf('%s (%s)', 'Boxberry', $this->title),
			'GPS_N' => $pointGps[0],
			'GPS_S' => $pointGps[1],
			'SCHEDULE' => $point['WorkSchedule'] ?? $point['WorkShedule'],
			'PHONE' => $point['Phone'],
			'DESCRIPTION' => $point['TripDescription'],
			'PROVIDER' => 'BOXBERRY',
		];
	}

	public function prepareCalculatePickup(int $deliveryId, string $storeId, string $locationId, string $zip = null) : void
	{
		$_SESSION['selPVZ'] = $storeId;
	}

	public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void
	{
		\CBoxberry::disableCheckPvz();

		[$zip, $city] = explode(',', $address, 2);

		if (!empty($zip))
		{
			$propZip = $order->getPropertyCollection()->getDeliveryLocationZip();

			if ($propZip !== null)
			{
				$propZip->setValue($zip);
			}
		}

		$propAddress = $order->getPropertyCollection()->getAddress();

		if ($propAddress === null) { return; }

		$propAddress->setValue(sprintf('Boxberry: %s #%s', $address, $storeId));
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}
}