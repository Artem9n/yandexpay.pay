<?php
/** @noinspection PhpUnused */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Dpd;

use Bitrix\Sale;
use Bitrix\Main;
use Ipolh\DPD\DB\Terminal;
use Ipolh\DPD\Delivery\DPD;
use YandexPay\Pay\Trading\Entity\Sale\Delivery as EntityDelivery;

class Pickup extends Base
{
	protected $codeService = 'ipolh_dpd:PICKUP';

	public function serviceType() : string
	{
		return EntityDelivery::PICKUP_TYPE;
	}

	public function markSelectedPickup(Sale\Order $order, string $storeId, string $address) : void
	{
		[$zip] = explode(',', $address, 2);

		if (!empty($zip))
		{
			$this->fillZip($order, $zip);
		}

		$this->fillAddress($order, sprintf('%s (%s)', $address, $storeId));

		$this->calculateAndFillSessionValues($order);

		$profile = DPD::getDeliveryProfile($this->codeService);
		$_REQUEST['IPOLH_DPD_TERMINAL'][$profile] = $storeId;
	}

	public function getStores(Sale\Order $order, Sale\Delivery\Services\Base $service, array $bounds) : array
	{
		$stores = $this->loadStores($bounds);

		if (empty($stores)) { return []; }

		return $stores;
	}

	/** @noinspection SpellCheckingInspection */
	protected function loadStores(array $bounds) : array
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
			$result[$pickup['LOCATION_ID']][] = $this->collectPoint($pickup);
		}

		return $result;
	}

	public function getDetailPickup(string $storeId) : array
	{
		$pickup = Terminal\Table::getByCode($storeId);

		return $this->collectPoint($pickup);
	}

	protected function collectPoint($pickup) : array
	{
		$schedule = implode(', ', array_filter($pickup['SCHEDULE_PAYMENTS']));

		return [
			'ID' => $pickup['CODE'],
			'ADDRESS' => $pickup['ADDRESS_FULL'],
			'TITLE' => sprintf('(%s) %s', $this->title, $pickup['NAME']),
			'GPS_N' => $pickup['LATITUDE'],
			'GPS_S' => $pickup['LONGITUDE'],
			'SCHEDULE' => $schedule,
			'PHONE' => $pickup['PHONE'] ?? '',
			'DESCRIPTION' => $pickup['ADDRESS_DESCR'],
			'PROVIDER' => $this->providerType(),
		];
	}

	public function providerType() : ?string
	{
		return null;
	}
}