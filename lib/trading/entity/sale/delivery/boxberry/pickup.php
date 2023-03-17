<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Boxberry;

use Bitrix\Sale;
use YandexPay\Pay\Data;
use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Entity\Sale\Delivery as EntityDelivery;

class Pickup extends Base
{
	protected $codeService = 'boxberry:PVZ';
    protected static $pickupList;

	public function serviceType() : string
	{
		return EntityDelivery::PICKUP_TYPE;
	}

	public function getStores(Sale\Order $order, Sale\Delivery\Services\Base $service, array $bounds) : array
	{
		$result = [];
		$storesByLocation = $this->loadStores($bounds);

		if (empty($storesByLocation)) { return $result; }

		$locationsIdsMap = $this->getLocationIdsByCodes(array_keys($storesByLocation));

		foreach ($storesByLocation as $locationCode => $stores)
		{
			$locationId = $locationsIdsMap[$locationCode];
			if (!isset($locationId)) { continue; }

			$result[$locationId] = $stores;
		}

		return $result;
	}

	protected function loadStores(array $bounds) : array
	{
		$result = [];

		\CBoxberry::initApi();

		$pickupList = \CBoxberry::methodExec('ListPoints', 36000, ['prepaid=1']);

		if (empty($pickupList) || !empty(static::$pickupList)) { return $result; }

		$metadata = new Data\Location\MetaData();
		$finder = new Data\Location\Bounds($metadata);

		foreach ($pickupList as $point)
		{
			$pointGps = explode(',', $point['GPS']);

			if ($pointGps[0] <= $bounds['ne']['latitude']
				&& $pointGps[0] >= $bounds['sw']['latitude']
				&& $pointGps[1] <= $bounds['ne']['longitude']
				&& $pointGps[1] >= $bounds['sw']['longitude'])
			{
				$locationCode = $finder->findClosestCity($pointGps[0], $pointGps[1]);

				if ($locationCode === null) { continue; }

				if (!isset($result[$locationCode])) { $result[$locationCode] = []; }

				$result[$locationCode][] = $this->collectPoint($point);
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

		$point['Code'] = $storeId;

		return $this->collectPoint($point);
	}

	public function markSelectedPickup(Sale\Order $order, string $storeId, string $address) : void
	{
		\CBoxberry::disableCheckPvz();

		[$zip] = explode(',', $address, 2);

		if (!empty($zip))
		{
			$this->fillZip($order, $zip);
		}

		$this->fillAddress($order, sprintf('Boxberry: %s #%s', $address, $storeId));
	}

	public function collectPoint(array $point) : array
	{
		$pointGps = explode(',', $point['GPS']);

		return [
			'ID' => $point['Code'],
			'ADDRESS' => $point['Address'] ?: $point['AddressReduce'],
			'TITLE' => sprintf('%s (%s)', 'Boxberry', $this->title),
			'GPS_N' => $pointGps[0],
			'GPS_S' => $pointGps[1],
			'SCHEDULE' => $point['WorkSchedule'] ?? $point['WorkShedule'],
			'PHONE' => $point['Phone'],
			'DESCRIPTION' => $point['TripDescription'],
			'PROVIDER' => $this->providerType(),
		];
	}

	public function prepareCalculatePickup(
		Sale\Order $order,
		int $deliveryId,
		string $pickupId,
		string $locationId,
		string $zip = null
	) : void
	{
		$_SESSION['selPVZ'] = $pickupId;
	}
}