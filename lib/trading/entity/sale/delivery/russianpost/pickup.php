<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\RussianPost;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Utils\Encoding;
use YandexPay\Pay\Data;
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

			$metadata = new Data\Location\MetaData();
			$finder = new Data\Location\Bounds($metadata);

			foreach ($pickupList['data'] as $pickup)
			{
				$locationCode = $finder->findClosestCity($pickup['geo']['coordinates'][1], $pickup['geo']['coordinates'][0]);

				if ($locationCode === null) { continue; }

				if (!isset($result[$locationCode])) { $result[$locationCode] = []; }

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

				$result[$locationCode][] = [
					'ID' => $pickup['id'],
					'REGION' => $pickup['address']['region'],
					'CITY' => $pickup['address']['place'],
					'STREET' => $pickup['address']['street'],
					'ADDRESS' => implode(', ', $address),
					'TITLE' => $this->title,
					'GPS_N' => $pickup['geo']['coordinates'][1], //latitude
					'GPS_S' => $pickup['geo']['coordinates'][0], //longitude
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

	public function prepareCalculatePickup(Sale\OrderBase $order, int $deliveryId, string $storeId, string $locationId, string $zip = null) : void
	{
		$tariff = $this->getTariff($order, $zip);

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

		$tariff = $this->getTariff($order, $zip);

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
}