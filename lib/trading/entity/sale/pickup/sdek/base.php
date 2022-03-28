<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup\Sdek;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Config;
use YandexPay\Pay\Trading\Entity\Sale\Pickup\AbstractAdapter;
use YandexPay\Pay\Trading\Entity\Sale\Pickup\Factory;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Base extends AbstractAdapter
{
	protected $title;

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
		if (!Main\Loader::includeModule('ipol.sdek')) { return []; }

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

		$query = \sqlSdekCity::select([], [
			'NAME' => $locationsName
		]);

		while ($location = $query->Fetch())
		{
			$result[$location['NAME']] = $location['BITRIX_ID'];
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
			$pickupList = \CDeliverySDEK::getListFile();

			if (empty($pickupList))
			{
				$cache->abortDataCache();
			}

			$cache->endDataCache($pickupList);
		}

		if (empty($pickupList) || $bounds === null) { return $result; }

		foreach ($pickupList['PVZ'] as $cityName => $pickupList)
		{
			foreach ($pickupList as $pickupKey => $pickup)
			{
				if (
					$pickup['cY'] <= $bounds['ne']['latitude']
					&& $pickup['cY'] >= $bounds['sw']['latitude']
					&& $pickup['cX'] <= $bounds['ne']['longitude']
					&& $pickup['cX'] >= $bounds['sw']['longitude']
				)
				{
					$result[$cityName][] = [
						'ID' => $pickupKey,
						'ADDRESS' => $pickup['Address'],
						'TITLE' => sprintf('(%s) %s', $this->title, $pickup['Name']),
						'GPS_N' => $pickup['cY'],
						'GPS_S' => $pickup['cX'],
						'SCHEDULE' => $pickup['WorkTime'],
						'PHONE' => $pickup['Phone'],
						'PROVIDER' => $this->getType()
					];
				}
			}
		}

		return $result;
	}

}