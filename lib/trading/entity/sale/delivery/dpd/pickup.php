<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Dpd;

use Bitrix\Main;
use Bitrix\Sale;
use Ipolh\DPD\DB\Terminal;
use \Ipolh\DPD\Delivery\DPD;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;

class Pickup extends Base
{
	protected $code = 'ipolh_dpd:PICKUP';

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}

	public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void
	{
		if ($propAddress = $order->getPropertyCollection()->getAddress())
		{
			$value = sprintf('%s (%s)', $address, $storeId);

			$propAddress->setValue($value);
		}

		/** @var Sale\Order $order */
		$this->calculateAndFillSessionValues($order);

		$profile = DPD::getDeliveryProfile($this->code);
		$_REQUEST['IPOLH_DPD_TERMINAL'][$profile] = $storeId;
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
				'ID', 'CODE', 'LOCATION_ID', 'NAME', 'ADDRESS_FULL', 'LATITUDE', 'LONGITUDE', 'ADDRESS_DESCR'
			]
		]);

		while ($pickup = $query->fetch())
		{
			$result[$pickup['LOCATION_ID']][] = $this->makePickupInfo($pickup);
		}

		return $result;
	}

	public function getDetailPickup(string $storeId) : array
	{
		if (!Main\Loader::includeModule('ipol.dpd')) { return []; }

		$pickup = Terminal\Table::getByCode($storeId);

		return $this->makePickupInfo($pickup);
	}

	private function makePickupInfo($pickup) : array
	{
		$schedule = implode(', ', array_filter($pickup['SCHEDULE_PAYMENTS']));

		$result = [
			'ID' => $pickup['CODE'],
			'ADDRESS' => $pickup['ADDRESS_FULL'],
			'TITLE' => sprintf('(%s) %s', $this->title, $pickup['NAME']),
			'GPS_N' => $pickup['LATITUDE'],
			'GPS_S' => $pickup['LONGITUDE'],
			'SCHEDULE' => $schedule,
			'PHONE' => $pickup['PHONE'] ?? '',
			'DESCRIPTION' => $pickup['ADDRESS_DESCR'],
			//'PROVIDER' => 'DPD',
		];

		return $result;
	}
}