<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup\Ozon;

use Ipol;
use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Config;
use YandexPay\Pay\Trading\Entity\Sale\Pickup\AbstractAdapter;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Base extends AbstractAdapter
{
	protected $title;
	protected $typeVariant;

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
		if (!Main\Loader::includeModule('ipol.ozon')) { return []; }

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

		if ($bounds === null) { return $result; }

		$type = Ipol\Ozon\Bitrix\Adapter::getVariantObjectTypesMap()[$this->typeVariant];

		$query =  Ipol\Ozon\VariantsTable::getList([
			'filter' => [
				'OBJECT_TYPE_ID' => $type,
				'<=LAT' => $bounds['ne']['latitude'],
				'>=LAT' => $bounds['sw']['latitude'],
				'<=LONG' => $bounds['ne']['longitude'],
				'>=LONG' => $bounds['sw']['longitude']
			],
			'select' => [
				'ID', 'DELIVERY_VARIANT_ID', 'ADDRESS', 'NAME', 'LAT', 'LONG', 'WORKING_HOURS', 'PHONE', 'SETTLEMENT',
				'HOW_TO_GET'
			]
		]);

		while ($pickup = $query->fetch())
		{
			$locationName = $pickup['SETTLEMENT'];

			$address = $pickup['ADDRESS'];
			$pos = mb_strpos($address, $locationName . ',');

			if ($pos !== false)
			{
				$address = mb_substr($address, $pos + mb_strlen($locationName) + 1);
				$address = trim($address, ' ,.');
			}

			$result[$locationName][] = [
				'ID' => $pickup['ID'],
				'ADDRESS' => $address,
				'TITLE' => sprintf('(%s) %s', $this->title, $pickup['NAME']),
				'GPS_N' => $pickup['LAT'],
				'GPS_S' => $pickup['LONG'],
				'SCHEDULE' => $pickup['WORKING_HOURS'],
				'PHONE' => $pickup['PHONE'],
				'DESCRIPTION' => $pickup['HOW_TO_GET'],
				'PROVIDER' => $this->getType(),
			];
		}

		return $result;
	}

}