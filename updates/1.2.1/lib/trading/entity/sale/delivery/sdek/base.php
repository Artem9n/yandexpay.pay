<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Sdek;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Config;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Base extends AbstractAdapter
{
	protected $title;
	protected $code;

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

		$pickupList = \CDeliverySDEK::getListFile();

		if (empty($pickupList) || $bounds === null) { return $result; }

		foreach ($pickupList[$this->code] as $cityName => $pickupList)
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
						'ADDRESS' => $cityName . ', ' . $pickup['Address'],
						'TITLE' => $this->title,
						'GPS_N' => $pickup['cY'],
						'GPS_S' => $pickup['cX'],
						'SCHEDULE' => $pickup['WorkTime'],
						'PHONE' => $pickup['Phone'],
						'PROVIDER' => $this->getType(),
						'DESCRIPTION' => $pickup['AddressComment'],
					];
				}
			}
		}

		return $result;
	}

	public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void
	{
		$tariff = $_SESSION['IPOLSDEK_CHOSEN']['pickup'];

		/** @var \Bitrix\Sale\PropertyValue $property */
		foreach ($order->getPropertyCollection() as $property)
		{
			if ($property->getField('CODE') !== 'IPOLSDEK_CNTDTARIF') { continue; }

			$property->setValue($tariff);
		}

		$propAddress = $order->getPropertyCollection()->getAddress();

		if ($propAddress === null) { return; }

		$propAddress->setValue(sprintf('%s #%s', $address, $storeId));
	}

	public function getDetailPickup(string $storeId) : array
	{
		if (!Main\Loader::includeModule('ipol.sdek')) { return []; }

		$result = [];

		$pickupList = \CDeliverySDEK::getListFile();

		if (empty($pickupList)) { return $result; }

		foreach ($pickupList[$this->code] as $cityName => $pickupList)
		{
			if (isset($pickupList[$storeId]))
			{
				$result = [
					'ID' => $storeId,
					'ADDRESS' => $cityName . ', ' . $pickupList[$storeId]['Address'],
					'TITLE' => $this->title,
					'GPS_N' => $pickupList[$storeId]['cY'],
					'GPS_S' => $pickupList[$storeId]['cX'],
					'SCHEDULE' => $pickupList[$storeId]['WorkTime'],
					'PHONE' => $pickupList[$storeId]['Phone'],
					'PROVIDER' => $this->getType(),
					'DESCRIPTION' => $pickupList[$storeId]['AddressComment'] ?: $pickupList[$storeId]['WorkTime'],
				];
				break;
			}
		}

		return $result;
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}
}