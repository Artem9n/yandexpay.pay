<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Boxberry;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Config;
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

	protected function getType() : string
	{
		return '';
	}

	public function getStores(Sale\OrderBase $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		if (!Main\Loader::includeModule('up.boxberrydelivery')) { return []; }

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
		$pickupList = [];

		$cache = Main\Data\Cache::createInstance();
		$cacheDir = Config::getModuleName() . ':' . $this->getType();

		if ($cache->initCache(36000, $this->getType(), $cacheDir))
		{
			$pickupList = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			\CBoxberry::initApi();

			$pickupList = \CBoxberry::methodExecPost('ListPoints', ['prepaid=1']);

			if (empty($pickupList))
			{
				$cache->abortDataCache();
			}

			$cache->endDataCache($pickupList);
		}

		if (empty($pickupList) || !empty(static::$pickupList)) { return $result; }

		foreach ($pickupList as $point)
		{
			$pointGps = explode(',', $point['GPS']);

			if ($pointGps[0] <= $bounds['ne']['latitude']
				&& $pointGps[0] >= $bounds['sw']['latitude']
				&& $pointGps[1] <= $bounds['ne']['longitude']
				&& $pointGps[1] >= $bounds['sw']['longitude'])
			{
				$result[$point['CityName']][] = array(
					'ID' => $point['Code'],
					'ADDRESS' => $point['Address'] ?: $point['AddressReduce'],
					'TITLE' => sprintf('(%s) %s', $this->title, $point['Name']),
					'GPS_N' => $pointGps[0],
					'GPS_S' => $pointGps[1],
					'SCHEDULE' => $point['WorkSchedule'],
					'PHONE' => $point['Phone'],
					'DESCRIPTION' => $point['TripDescription'],
					'PROVIDER' => $this->getType(),
				);
			}
		}

		static::$pickupList = $result;

		return $result;
	}

	public function markSelected(Sale\OrderBase $order, array $store = []) : void
	{
		$propAddress = $order->getPropertyCollection()->getAddress();

		if ($propAddress === null) { return; }

		$propAddress->setValue(sprintf('Boxberry: %s #%s', $store['address'], $store['storeId']));
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}
}