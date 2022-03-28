<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup\Dpd;

use Bitrix\Main;
use Bitrix\Sale;
use Ipolh\DPD\DB\Terminal;
use YandexPay\Pay\Config;
use YandexPay\Pay\Trading\Entity\Sale\Pickup\AbstractAdapter;
use YandexPay\Pay\Trading\Entity\Sale\Pickup\Factory;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Pickup extends AbstractAdapter
{
	protected $title;

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getNameWithParent();

		return $code === 'ipolh_dpd:PICKUP';
	}

	public function getStores(Sale\OrderBase $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		if (!Main\Loader::includeModule('ipol.dpd')) { return []; }

		$stores = $this->loadStores($bounds);

		if (empty($stores)) { return []; }

		return $stores;
	}

	protected function loadStores(array $bounds = null) : array
	{
		$result = [];

		$query =  Terminal\Table::getList([
			'filter' => [
				'<=LATITUDE' => $bounds['ne']['latitude'],
				'>=LATITUDE' => $bounds['sw']['latitude'],
				'<=LONGITUDE' => $bounds['ne']['longitude'],
				'>=LONGITUDE' => $bounds['sw']['longitude'],
			],
			'select' => [
				'ID', 'LOCATION_ID', 'NAME', 'ADDRESS_FULL', 'LATITUDE', 'LONGITUDE', 'ADDRESS_DESCR'
			]
		]);

		while ($pickup = $query->fetch())
		{
			$schedule = implode(', ', array_filter($pickup['SCHEDULE_PAYMENTS']));

			$result[$pickup['LOCATION_ID']][] = [
				'ID' => $pickup['ID'],
				'ADDRESS' => $pickup['ADDRESS_FULL'],
				'TITLE' => sprintf('(%s) %s', $this->title, $pickup['NAME']),
				'GPS_N' => $pickup['LATITUDE'],
				'GPS_S' => $pickup['LONGITUDE'],
				'SCHEDULE' => $schedule,
				'PHONE' => $pickup['PHONE'] ?? '',
				'DESCRIPTION' => $pickup['ADDRESS_DESCR'],
				'PROVIDER' => Factory::DPD_PICKUP,
			];
		}

		return $result;
	}

}