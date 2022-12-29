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

		if (CheckVersion(Main\ModuleManager::getVersion('ipol.sdek'), '3.11.7'))
		{
			$pvzController = new \Ipolh\SDEK\Bitrix\Controller\pvzController(true);
			$list = $pvzController->getList();
			$needDecode = true;
		}
		else
		{
			$list = \CDeliverySDEK::getListFile();
			$needDecode = false;
		}

		$weight = !empty(\CDeliverySDEK::$orderWeight) ? false : \COption::GetOptionString(\CDeliverySDEK::$MODULE_ID, 'weightD', 1000);

		$pickupList = method_exists(\CDeliverySDEK::class, 'weightPVZ')
			? \CDeliverySDEK::weightPVZ($weight, $list[$this->code])
			: \CDeliverySDEK::wegihtPVZ($weight, $list[$this->code]);

		if (empty($pickupList) || $bounds === null) { return $result; }

		foreach ($pickupList as $cityId => $pickups)
		{
			$cityName = $list['CITY'][$cityId] ?? $cityId;

			foreach ($pickups as $pickupKey => $pickup)
			{
				if (
					$pickup['cY'] <= $bounds['ne']['latitude']
					&& $pickup['cY'] >= $bounds['sw']['latitude']
					&& $pickup['cX'] <= $bounds['ne']['longitude']
					&& $pickup['cX'] >= $bounds['sw']['longitude']
				)
				{
					$store = [
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

					if ($needDecode)
					{
						/** @var string $cityName */
						$cityNameDecoded = \Ipolh\SDEK\Bitrix\Tools::encodeFromUTF8($cityName);
						$store = \Ipolh\SDEK\Bitrix\Tools::encodeFromUTF8($store);
					}
					else
					{
						$cityNameDecoded = $cityName;
					}

					if (!isset($result[$cityNameDecoded])) { $result[$cityNameDecoded] = []; }

					$result[$cityNameDecoded][] = $store;
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
		$propAddress = $this->addressProperty($order);

		if ($propAddress === null) { return; }

		$propAddress->setValue($value);
	}

	protected function getAddressCode(Sale\OrderBase $order) : string
	{
		return (string)\Ipolh\SDEK\option::get('pvzPicker');
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