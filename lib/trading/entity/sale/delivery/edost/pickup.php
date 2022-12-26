<?php
/** @noinspection PhpUnused */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Edost;

use Bitrix\Sale;
use Ipolh\DPD\DB\Terminal;
use Ipolh\DPD\Delivery\DPD;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class Pickup extends Base
{
	protected $code = 'edost:PICKUP';

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
	}

	public function getStores(Sale\OrderBase $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		$stores = $this->loadStores($order, $bounds);

		if (empty($stores)) { return []; }

		return $stores;
	}

	/** @noinspection SpellCheckingInspection */
	protected function loadStores(Sale\OrderBase $order, array $bounds = null) : array
	{
		$result = [];

		if (class_exists('edost_class') && class_exists('CDeliveryEDOST'))
		{
			$config = new Config();

			$data = $this->getOffice($config, $order);

			foreach ($data['data'][30] as $pickup)
			{
				$result[2440][] = $this->makePickupInfo($pickup);
			}
			foreach ($data['data'][23] as $pickup)
			{
				$result[2440][] = $this->makePickupInfo($pickup);
			}
			foreach ($data['data'][5] as $pickup)
			{
				$result[2440][] = $this->makePickupInfo($pickup);
			}
		}

		return $result;
	}

	protected function getCompanies(Config $config, Sale\OrderBase $order) : array
	{
		$office = [];

		$parameters = [
			'country' => 0,
			'region' => 38,
			'city' => urlencode('Иркутск'),
			'weight' => urlencode($order->getBasket()->getWeight() / 1000),
			'insurance' => urlencode($order->getBasket()->getPrice()),
			'size' => urlencode(implode('|', $this->getDimensions($order))),
		];

		$data = \edost_class::RequestData(
			'',
			$config->getId(),
			$config->getKey(),
			implode('&', array_map(function($key, $value) { return $key . '=' . $value; }, array_keys($parameters), $parameters)),
			'delivery'
		);

		if (!empty($data['data']))
		{
			foreach ($data['data'] as $item)
			{
				$office[] = $item['company_id'];
			}

			$office = array_unique($office);
		}

		return $office;
	}

	protected function getOffice(Config $config, Sale\OrderBase $order) : array
	{
		$companies = $this->getCompanies($config, $order);
		$dimensions = $this->getDimensions($order);

		$edostOrderData = [
			'location' => \CDeliveryEDOST::GetEdostLocation(2440),
			'zip' => '',
			'weight' => $order->getBasket()->getWeight() / 1000,
			'price' => $order->getBasket()->getPrice(),
			'sizesum' => array_sum($dimensions),
			'size1' => array_shift($dimensions),
			'size2' => array_shift($dimensions),
			'size3' => array_shift($dimensions),
			'config' => $config->getConfig() + [
					'PAY_SYSTEM_ID' => '76',
					'COMPACT' => 'off',
					'PRIORITY' => 'P',
				],
			'original' => [
				'PRICE' => 2.85,
				'WEIGHT' => 100.0,
				'LOCATION_FROM' => '0000073738',
				'SITE_ID' => 's1',
				'PERSON_TYPE_ID' => 1,
				'CURRENCY' => 'RUB',
				'LOCATION_TO' => '0000876108',
				'LOCATION_ZIP' => '00000',
				'ITEMS' => [
					[
						'PRICE' => 2.85,
						'CURRENCY' => 'RUB',
						'WEIGHT' => 100.0,
						'QUANTITY' => 1.0,
						'DELAY' => 'N',
						'CAN_BUY' => 'Y',
						'DIMENSIONS' => 'xx',
						'NAME' => 'Футболка Мужская огонь',
					],
				],
			],
		];

		return \edost_class::GetOffice($edostOrderData, $companies);
	}

	protected function getDimensions(Sale\OrderBase $order) : array
	{
		static $dimensionsResult;

		if ($dimensionsResult === null)
		{
			$width = $height = $length = 0;

			/** @var \Bitrix\Sale\BasketItem $item */
			foreach ($order->getBasket() as $item)
			{
				$dimensions = $item->getField('DIMENSIONS');
				if (!empty($dimensions))
				{
					$dimensions = unserialize($dimensions);

					$width += $dimensions['WIDTH'];
					$height += $dimensions['HEIGHT'];
					$length += $dimensions['LENGTH'];
				}
			}

			$dimensionsResult = [$height, $width, $length];
		}

		return $dimensionsResult;
	}

	protected function getLocation(Sale\OrderBase $order) : array
	{
		$location = [];

		return $location;
	}

	public function getDetailPickup(string $storeId) : array
	{
		$pickup = null;

		return $this->makePickupInfo($pickup);
	}

	/** @noinspection SpellCheckingInspection */
	private function makePickupInfo($pickup) : array
	{
		$coords = explode(',', $pickup['gps']);

		$result = [
			'ID' => $pickup['code'],
			'ADDRESS' => $pickup['address_full'],
			'TITLE' => sprintf('(%s) %s', $this->title, $pickup['name'] ?: $pickup['code']),
			'GPS_N' => $coords[1],
			'GPS_S' => $coords[0],
			'SCHEDULE' => $pickup['schedule'],
			'PHONE' => $pickup['tel'] ?? '',
			//'DESCRIPTION' => $pickup['ADDRESS_DESCR'],
			//'PROVIDER' => 'eDost',
		];

		return $result;
	}
}