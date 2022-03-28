<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup\RussianPost;

use Bitrix\Main;
use Bitrix\Sale;
use Russianpost\Post\Request;
use YandexPay\Pay\Trading\Entity\Sale\Pickup\AbstractAdapter;

/** @property \Sale\Handlers\Delivery\RussianpostProfile $service */
class RussianPost extends AbstractAdapter
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

		$russianPost = new Request();
		echo '<pre>';
		var_dump($russianPost->PickUpWorldCalculate(['ADDRESS' => 'Иркутск', 'PRICE' => 250, 'WEIGHT' => '10']));
		echo '</pre>';
		die;

		/*if ($bounds === null) { return $result; }

		$type = Ipol\Ozon\Bitrix\Adapter::getVariantObjectTypesMap()['pickup'];

		$query =  Ipol\Ozon\VariantsTable::getList([
			'filter' => [
				'OBJECT_TYPE_ID' => $type,
				['<=LAT' => $bounds['ne']['latitude']],
				['>=LAT' => $bounds['sw']['latitude']],
				['<=LONG' => $bounds['ne']['longitude']],
				['>=LONG' => $bounds['sw']['longitude']],
			],
			'select' => [
				'ID', 'DELIVERY_VARIANT_ID', 'ADDRESS', 'NAME', 'LAT', 'LONG', 'WORKING_HOURS', 'PHONE', 'SETTLEMENT',
				'HOW_TO_GET'
			]
		]);

		while ($pickup = $query->fetch())
		{
			$locationName = $pickup['SETTLEMENT'];

			$address = $pickup['ADDRESS'];
			$pos = mb_strpos($address, $locationName . ',');

			if ($pos !== false)
			{
				$address = mb_substr($address, $pos + mb_strlen($locationName) + 1);
				$address = trim($address, ' ,.');
			}

			$result[$locationName][] = array(
				'ID' => $pickup['ID'],
				'ADDRESS' => $address,
				'TITLE' => sprintf('(%s) %s', $this->title, $pickup['NAME']),
				'GPS_N' => $pickup['LAT'],
				'GPS_S' => $pickup['LONG'],
				'SCHEDULE' => $pickup['WORKING_HOURS'],
				'PHONE' => $pickup['PHONE'],
				'DESCRIPTION' => $pickup['HOW_TO_GET']
			);
		}*/

		return $result;
	}

}