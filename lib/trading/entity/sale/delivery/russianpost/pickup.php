<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\RussianPost;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;
use YandexPay\Pay\Utils\Encoding;
use YandexPay\Pay\Data;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property \Sale\Handlers\Delivery\RussianpostProfile $service */
class Pickup extends Base
{
	protected $accountId = 'pickupId';
	protected $codeService = 'POST';

	public function serviceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
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

	protected function loadStores(array $bounds) : array
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

				$result[$locationCode][] = $this->collectPickup($pickup);
			}
		}
		catch (Main\SystemException $exception)
		{
			trigger_error('loadStores: ' . $exception->getMessage(), E_USER_WARNING);
		}

		return $result;
	}

	public function getDetailPickup(string $storeId) : array
	{
		$result = [];

		try
		{
			$httpClient = new Main\Web\HttpClient();
			$pickup = Main\Web\Json::decode($httpClient->get('https://widget.pochta.ru/api/pvz/' . $storeId));

			return $this->collectPickup($pickup);
		}
		catch (Main\SystemException $exception)
		{
			trigger_error('getDetailPickup: ' . $exception->getMessage(), E_USER_WARNING);
		}

		return $result;
	}

	public function prepareCalculatePickup(
		Sale\Order $order,
		int $deliveryId,
		string $pickupId,
		string $locationId,
		string $zip = null
	) : void
	{
		$tariff = $this->getTariff($order, $zip);

		$_REQUEST['order']['russianpost_result_price'] = $tariff['tariff']['total'];
		$_REQUEST['order']['DELIVERY_ID'] = $deliveryId;
		$_REQUEST['order']['russianpost_select_pvz'] = 'Y';
		$_REQUEST['order']['russianpost_result_address'] = ' ';
		$_REQUEST['order']['russianpost_delivery_description'] = Encoding::convert($tariff['delivery']['description']);
	}

	public function markSelectedPickup(Sale\Order $order, string $storeId, string $address) : void
	{
		[$zip] = explode(',', $address, 2);

		if (!empty($zip))
		{
			$this->fillTariff($order, $zip);
			$this->fillZip($order, $zip);
		}

		$this->fillAddress($order, $address);
	}

	public function collectPickup(array $pickup) : array
	{
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

		return [
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
			'PROVIDER' => $this->providerType(),
			'ZIP' => $pickup['address']['index'],
		];
	}
}