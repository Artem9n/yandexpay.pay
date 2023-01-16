<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\RussianPost;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;
use YandexPay\Pay\Utils\Encoding;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property \Sale\Handlers\Delivery\RussianpostProfile $service */
class Pickup extends Base
{
	protected $accountId = 'pickupId';

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof \Sale\Handlers\Delivery\RussianpostProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getNameWithParent();

		return $code === 'POST';
	}

	public function getStores(Sale\Order $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
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

		$query = Sale\Location\Name\LocationTable::getList([
			'filter' => [
				'NAME' => $locationsName,
				'=LANGUAGE_ID' => 'ru',
			],
			'select' => [
				'LOCATION_ID',
				'NAME',
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

		$account = $this->getAccount();

		try
		{
			$httpClient = new Main\Web\HttpClient();

			$url = sprintf(
				'https://widget.pochta.ru/api/pvz?settings_id=%s&pvzType[]=russian_post%s&currentTopRightPoint[]=%s&currentTopRightPoint[]=%s&currentBottomLeftPoint[]=%s&currentBottomLeftPoint[]=%s',
				$account[$this->accountId],
				$account['showPostamat'] ? '&pvzType[]=postamat' : '',
				$bounds['ne']['longitude'],
				$bounds['ne']['latitude'],
				$bounds['sw']['longitude'],
				$bounds['sw']['latitude']
			);

			$pickupList = Main\Web\Json::decode($httpClient->get($url));

			if (empty($pickupList['data'])) { return $result; }

			foreach ($pickupList['data'] as $pickup)
			{
				$cityName = $this->getCityName($pickup['address']['place']);

				$address = array_filter([
					$pickup['address']['index'],
					$pickup['address']['region'],
					$pickup['address']['place'],
					$pickup['address']['location'],
					$pickup['address']['street'],
					$pickup['address']['house'],
					$pickup['address']['room'],
					$pickup['address']['hotel'],
					$pickup['address']['corpus'],
				]);

				$result[$cityName][] = [
					'ID' => $pickup['id'],
					'REGION' => $pickup['address']['region'],
					'CITY' => $pickup['address']['place'],
					'STREET' => $pickup['address']['street'],
					'ADDRESS' => implode(', ', $address),
					'TITLE' => $this->title,
					'GPS_N' => $pickup['geo']['coordinates'][1],
					'GPS_S' => $pickup['geo']['coordinates'][0],
					'SCHEDULE' => '',
					'PHONE' => $pickup['PHONE'] ?? '',
					'DESCRIPTION' => implode(', ', $pickup['workTime']),
					'PROVIDER' => 'RUSSIAN_POST',
					'ZIP' => $pickup['address']['index'],
				];
			}
		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}

		return $result;
	}

	public function getDetailPickup(string $storeId) : array
	{
		$result = [];

		try
		{
			$httpClient = new Main\Web\HttpClient();
			$point = Main\Web\Json::decode($httpClient->get('https://widget.pochta.ru/api/pvz/' . $storeId));

			$address = array_filter([
				$point['address']['index'],
				$point['address']['region'],
				$point['address']['place'],
				$point['address']['location'],
				$point['address']['street'],
				$point['address']['house'],
				$point['address']['room'],
				$point['address']['hotel'],
				$point['address']['corpus'],
			]);

			$result = [
				'ID' => $storeId,
				'ADDRESS' => implode(', ', $address),
				'TITLE' => sprintf('%s (%s)', 'Russian post', $this->title),
				'GPS_N' => $point['geo']['coordinates'][1],
				'GPS_S' => $point['geo']['coordinates'][0],
				'SCHEDULE' => '',
				'PHONE' => '',
				'DESCRIPTION' => implode(', ', $point['workTime']),
				'PROVIDER' => 'Russian post',
				'ZIP' => $point['address']['index'],
			];
		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}

		return $result;
	}

	public function prepareCalculatePickup(int $deliveryId, string $storeId, string $locationId, string $zip = null) : void
	{
		$tariff = $this->getTariff($zip);

		$_REQUEST['order']['russianpost_result_price'] = $tariff['tariff']['total'];
		$_REQUEST['order']['DELIVERY_ID'] = $deliveryId;
		$_REQUEST['order']['russianpost_select_pvz'] = 'Y';
		$_REQUEST['order']['russianpost_result_address'] = ' ';
		$_REQUEST['order']['russianpost_delivery_description'] = Encoding::convert($tariff['delivery']['description']);
	}

	public function markSelected(Sale\Order $order, string $storeId = null, string $address = null) : void
	{
		[$zip, $city] = explode(',', $address, 2);

		if (!empty($zip))
		{
			$propZip = $this->zipProperty($order);

			if ($propZip !== null)
			{
				$propZip->setValue($zip);
			}
		}

		$tariff = $this->getTariff($zip);

		if ($tariff !== null)
		{
			/** @var \Bitrix\Sale\PropertyValue $property */
			foreach ($order->getPropertyCollection() as $property)
			{
				if ($property->getField('CODE') === 'RUSSIANPOST_TYPEDLV')
				{
					$property->setValue($tariff['type']);
					break;
				}
			}
		}

		$propAddress = $this->addressProperty($order);

		if ($propAddress === null) { return; }

		$propAddress->setValue($address);
	}

	protected function getZipCode(Sale\Order $order) : string
	{
		return (string)\Russianpost\Post\Optionpost::get('zip', true, $order->getSiteId());
	}

	protected function getAddressCode(Sale\Order $order) : string
	{
		return (string)\Russianpost\Post\Optionpost::get('address', true, $order->getSiteId());
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}

	public function getCityName(string $city) : string
	{
		$parts = explode(' ', $city, 2);

		if (mb_strlen($parts[0]) <= 3 && mb_strtolower($parts[0]) === $parts[0])
		{
			$city = $parts[1];
		}

		return $city;
	}
}