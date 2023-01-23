<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Boxberry;

use Bitrix\Sale;
use Bitrix\Main;
use YandexPay\Pay\Data;
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
		return $this->loadStores($bounds);
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

	protected function loadStores(array $bounds = null) : array
	{
		$result = [];

		\CBoxberry::initApi();

		$pickupList = \CBoxberry::methodExec('ListPoints', 36000, ['prepaid=1']);

		$metadata = new Data\Location\MetaData();
		$finder = new Data\Location\Bounds($metadata);

		$locations = $finder->search(
			$bounds['sw']['latitude'],
			$bounds['sw']['longitude'],
			$bounds['ne']['latitude'],
			$bounds['ne']['longitude']);

		if (empty($locations)) { return $result; }

		$locationsIdsMap = $this->getLocationIdsByCodes(array_keys($locations));

		if (empty($pickupList) || !empty(static::$pickupList)) { return $result; }

		foreach ($pickupList as $point)
		{
			$pointGps = explode(',', $point['GPS']);

			if ($pointGps[0] <= $bounds['ne']['latitude']
				&& $pointGps[0] >= $bounds['sw']['latitude']
				&& $pointGps[1] <= $bounds['ne']['longitude']
				&& $pointGps[1] >= $bounds['sw']['longitude'])
			{
				$locationCode = $finder->findClosestCity($locations, $pointGps[0], $pointGps[1]);

				if ($locationCode === null) { continue; }

				$locationId = $locationsIdsMap[$locationCode];

				$result[$locationId][] = [
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
			throw new DtoProperty(($point['err'] ?? 'not detail point: ') . $storeId);
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

	public function prepareCalculatePickup(Sale\OrderBase $order, int $deliveryId, string $storeId, string $locationId, string $zip = null) : void
	{
		$_SESSION['selPVZ'] = $storeId;
	}

	public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void
	{
		\CBoxberry::disableCheckPvz();

		[$zip, $city] = explode(',', $address, 2);

		if (!empty($zip))
		{
			$propZip = $this->zipProperty($order);

			if ($propZip !== null)
			{
				$propZip->setValue($zip);
			}
		}

		$propAddress = $this->addressProperty($order);

		if ($propAddress === null) { return; }

		$propAddress->setValue(sprintf('Boxberry: %s #%s', $address, $storeId));
	}

	protected function getZipCode(Sale\OrderBase $order) : string
	{
		return Main\Config\Option::get(\CBoxberry::$moduleId, 'BB_ZIP');
	}

	protected function getAddressCode(Sale\OrderBase $order) : string
	{
		return Main\Config\Option::get(\CBoxberry::$moduleId, 'BB_ADDRESS');
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}
}