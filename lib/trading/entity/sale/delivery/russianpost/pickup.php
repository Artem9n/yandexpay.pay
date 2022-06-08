<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\RussianPost;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;
use YandexPay\Pay\Utils\Encoding;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property \Sale\Handlers\Delivery\RussianpostProfile $service */
class Pickup extends AbstractAdapter
{
	protected $title;

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof \Sale\Handlers\Delivery\RussianpostProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return $code === 'POST';
	}

	public function getStores(Sale\OrderBase $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		if (!Main\Loader::includeModule('russianpost.post')) { return []; }

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

		try
		{
			$httpClient = new Main\Web\HttpClient();

			$url = sprintf(
				'https://widget.pochta.ru/api/pvz?currentTopRightPoint[]=%s&currentTopRightPoint[]=%s&currentBottomLeftPoint[]=%s&currentBottomLeftPoint[]=%s',
				$bounds['ne']['longitude'],
				$bounds['ne']['latitude'],
				$bounds['sw']['longitude'],
				$bounds['sw']['latitude']
			);

			$pickupList = Main\Web\Json::decode($httpClient->get($url));

			if (empty($pickupList['data'])) { return $result; }

			foreach ($pickupList['data'] as $pickup)
			{
				$cityName = str_replace(Encoding::convert('г '), '', $pickup['address']['place']);

				$address = array_filter([
					$pickup['address']['region'],
					$pickup['address']['place'],
					$pickup['address']['street'],
					$pickup['address']['house'],
					$pickup['address']['room'],
					$pickup['address']['hotel'],
					$pickup['address']['corpus'],
					$pickup['deliveryPointIndex'],
				]);

				$result[$cityName][] = [
					'ID' => $pickup['id'],
					'REGION' => $pickup['address']['region'],
					'CITY' => $pickup['address']['place'],
					'STREET' => $pickup['address']['street'],
					'ADDRESS' => implode(', ', $address),
					'TITLE' => sprintf('(%s) %s', $this->title, $pickup['address']['street']),
					'GPS_N' => $pickup['geo']['coordinates'][1],
					'GPS_S' => $pickup['geo']['coordinates'][0],
					'SCHEDULE' => '',
					'PHONE' => $pickup['PHONE'] ?? '',
					'DESCRIPTION' => $pickup['workTime'] ?? '',
					'PROVIDER' => Factory::RUSSIAN_POST,
					'ZIP' => $pickup['deliveryPointIndex'],
				];
			}
		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}

		return $result;
	}

	protected function getDetailPickup(int $pickupId) : array
	{
		$result = [];

		try
		{
			$httpClient = new Main\Web\HttpClient();
			$result = Main\Web\Json::decode($httpClient->get('https://widget.pochta.ru/api/pvz/' . $pickupId));
		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}

		return $result;
	}

	public function markSelected(Sale\OrderBase $order, array $store = []) : void
	{
		// TODO: Implement markSelected() method.
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}
}