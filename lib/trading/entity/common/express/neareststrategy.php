<?php
namespace YandexPay\Pay\Trading\Entity\Common\Express;

use Bitrix\Catalog;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Rest\Dto;
use YandexPay\Pay\Trading\Settings\Options;

class NearestStrategy extends AbstractStrategy
{
	use Concerns\HasMessage;

	/** @var array */
	protected $stores;

	public function title() : string
	{
		return self::getMessage('TITLE');
	}

	public function resolve(array $storeIds, Dto\Address $address = null, array $context = []) : ?array
	{
		$stores = $this->getStores($storeIds, $context);

		if (empty($stores)) { return null; }

		if ($address === null)
		{
			$result = reset($stores);
		}
		else
		{
			$warehouse = new Options\Warehouse();
			$distance = [];

			foreach ($stores as $storeId => $store)
			{
				$warehouse->setValues($store[$context['WAREHOUSE_FIELD']]);
				$validateResult = $warehouse->validate();

				if (!$validateResult->isSuccess()) { continue; }

				$distance[$storeId] = $this->getDistance(
					$address->getLat(),
					$address->getLon(),
					$warehouse->getLat(),
					$warehouse->getLon()
				);
			}

			if (empty($distance)) { return null; }

			$key = array_keys($distance, min($distance))[0];
			$result = $stores[$key] ?? null;
		}

		return $result;
	}

	public function getStores(array $storeIds, array $context) : array
	{
		if ($this->stores === null)
		{
			$this->stores = $this->loadStores($storeIds, $context);
		}

		return $this->stores;
	}

	protected function loadStores(array $storeIds, array $context) : array
	{
		$result = [];

		$query = Catalog\StoreTable::getList([
			'filter' => [
				'ACTIVE' => 'Y',
				'ID' => $storeIds,
				[
					'LOGIC' => 'OR',
					['=SITE_ID' => $context['SITE_ID']],
					['SITE_ID' => false],
				],
				'!' . $context['WAREHOUSE_FIELD'] => false,
				'!' . $context['CONTACT_FIELD'] => false,
				'!' . $context['SHIPMENT_SCHEDULE_FIELD'] => false,
			],
			'select' => ['ID', 'SITE_ID', $context['WAREHOUSE_FIELD'], $context['CONTACT_FIELD'], $context['SHIPMENT_SCHEDULE_FIELD']],
			'order' => [ 'SORT' => 'ASC' ],
		]);

		while ($store = $query->fetch())
		{
			$warehouse = $store[$context['WAREHOUSE_FIELD']];
			$schedule = $store[$context['SHIPMENT_SCHEDULE_FIELD']];

			if (!is_array($warehouse))
			{
				$store[$context['WAREHOUSE_FIELD']] = unserialize($warehouse, [ 'allowed_classes' => false ]);
			}

			if (!is_array($schedule))
			{
				$store[$context['SHIPMENT_SCHEDULE_FIELD']] = unserialize($schedule, [ 'allowed_classes' => false ]);
			}

			$result[$store['ID']] = $store;
		}

		return $result;
	}

	public function getDistance(
		float $lat1,
		float $lon1,
		float $lat2,
		float $lon2
	) : float
	{
		$lat1 *= M_PI / 180;
		$lat2 *= M_PI / 180;
		$lon1 *= M_PI / 180;
		$lon2 *= M_PI / 180;

		$d_lon = $lon1 - $lon2;

		$slat1 = sin($lat1);
		$slat2 = sin($lat2);
		$clat1 = cos($lat1);
		$clat2 = cos($lat2);
		$sdelt = sin($d_lon);
		$cdelt = cos($d_lon);

		$y = (($clat2 * $sdelt) ** 2) + (($clat1 * $slat2 - $slat1 * $clat2 * $cdelt) ** 2);
		$x = $slat1 * $slat2 + $clat1 * $clat2 * $cdelt;

		return atan2(sqrt($y), $x) * 6372795;
	}
}