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

	public function load() : bool
	{
		return Main\Loader::includeModule('ipol.sdek');
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

		$list = \CDeliverySDEK::getListFile();

		$weight = !empty(\CDeliverySDEK::$orderWeight) ? false : \Ipolh\SDEK\option::get('weightD');

		$pickupList = \CDeliverySDEK::weightPVZ($weight, $list[$this->code]);

		if (empty($pickupList) || $bounds === null) { return $result; }

		foreach ($pickupList as $cityName => $pickups)
		{
			foreach ($pickups as $pickupKey => $pickup)
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
						'PROVIDER' => 'CDEK',
						'DESCRIPTION' => $pickup['AddressComment'],
					];
				}
			}
		}

		return $result;
	}

	public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void
	{
		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $order->getShipmentCollection();

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem()) { continue; }

			$shipment->calculateDelivery();
		}

		$tariff = $_SESSION['IPOLSDEK_CHOSEN']['pickup'];

		/** @var \Bitrix\Sale\PropertyValue $property */
		foreach ($order->getPropertyCollection() as $property)
		{
			if ($property->getField('CODE') !== 'IPOLSDEK_CNTDTARIF') { continue; }

			$property->setValue($tariff);
		}

		$value = sprintf('%s #S%s', $address, $storeId);
		$sdekAddressCode = \Ipolh\SDEK\option::get('pvzPicker');
		$propertyCollection = $order->getPropertyCollection();
		$propAddress = null;

		if ($sdekAddressCode)
		{
			foreach ($propertyCollection as $property)
			{
				if ($property->getField('CODE') !== $sdekAddressCode) { continue; }

				$propAddress = $property;
				break;
			}
		}

		if ($propAddress === null)
		{
			$propAddress = $propertyCollection->getAddress();

			if ($propAddress === null) { return; }
		}

		$propAddress->setValue($value);
	}

	public function getDetailPickup(string $storeId) : array
	{
		$result = [];

		$pickupList = \CDeliverySDEK::getListFile();

		if (empty($pickupList)) { return $result; }

		foreach ($pickupList[$this->code] as $cityName => $pickupList)
		{
			$pickup = $pickupList[$storeId];

			if (isset($pickup))
			{
				$result = [
					'ID' => $storeId,
					'ADDRESS' => $cityName . ', ' . $pickup['Address'],
					'TITLE' => $this->title,
					'GPS_N' => $pickup['cY'],
					'GPS_S' => $pickup['cX'],
					'SCHEDULE' => $pickup['WorkTime'],
					'PHONE' => $pickup['Phone'],
					'PROVIDER' => 'CDEK',
					'DESCRIPTION' => sprintf('%s %s', $pickup['AddressComment'], $pickup['WorkTime']),
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