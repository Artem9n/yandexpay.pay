<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Sdek;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Pickup extends Base
{
	protected $code = 'PVZ';
	protected $codeService = 'sdek:pickup';
	protected $tariff = 'pickup';

	public function serviceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}

	public function getStores(Sale\Order $order, Sale\Delivery\Services\Base $service, array $bounds) : array
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

	protected function loadStores(array $bounds) : array
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

		if (empty($pickupList)) { return $result; }

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
					$pickup['id'] = $pickupKey;
					$pickup['address'] = $cityName . ', ' . $pickup['Address'];

					$store = $this->collectPickup($pickup);

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

	public function markSelectedPickup(Sale\Order $order, string $storeId, string $address) : void
	{
		$this->fillTariff($order);
		$this->fillAddress($order, sprintf('%s #S%s', $address, $storeId));
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
				$pickup['id'] = $storeId;
				$pickup['address'] = $cityName . ', ' . $pickup['Address'];

				$result = $this->collectPickup($pickup);
				break;
			}
		}

		return $result;
	}

	protected function addressCode(Sale\Order $order) : string
	{
		return (string)\Ipolh\SDEK\option::get('pvzPicker');
	}

	protected function collectPickup(array $pickup) : array
	{
		return [
			'ID' => $pickup['id'],
			'ADDRESS' => $pickup['address'],
			'TITLE' => $this->title,
			'GPS_N' => $pickup['cY'],
			'GPS_S' => $pickup['cX'],
			'SCHEDULE' => $pickup['WorkTime'],
			'PHONE' => $pickup['Phone'],
			'PROVIDER' => $this->providerType(),
			'DESCRIPTION' => sprintf('%s %s', $pickup['AddressComment'], $pickup['WorkTime']),
		];
	}
}