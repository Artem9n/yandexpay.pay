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
		$stores = $this->loadStores($bounds);

		if (empty($stores)) { return []; }

		return $stores;
	}

	/** @noinspection SpellCheckingInspection */
	protected function loadStores(array $bounds = null) : array
	{
		$result = [];

		if (class_exists('edost_class') && class_exists('CDeliveryEDOST'))
		{
			$edost_order = [
				'location' => [
					'id' => 2440,
					'country' => 0,
					'region' => 38,
					'city' => 'Иркутск',
					'country_name' => 'Россия',
					'region_name' => 'Иркутская область',
					'bitrix' => [
						'country' => 1,
						'region' => 69,
						'city' => 'Иркутск'
					]
				],
				'zip' => '',
				'weight' => 0.1,
				'price' => 2.85,
				'size1' => 0.0,
				'size2' => 0.0,
				'size3' => 0.0,
				'sizesum' => 0.0,
				'config' => [
					'id' => '9481',
					'ps' => 'Z9aJ9UTavqNzH8AtmhfzezozjqycvctB',
					'host' => '',
					'hide_error' => 'N',
					'show_zero_tariff' => 'Y',
					'map' => 'Y',
					'cod_status' => '',
					'send_zip' => 'Y',
					'hide_payment' => 'Y',
					'sort_ascending' => 'N',
					'template' => 'N3',
					'template_format' => 'off',
					'template_block' => 'off',
					'template_block_type' => 'none',
					'template_cod' => 'off',
					'template_autoselect_office' => 'N',
					'autoselect' => 'N',
					'admin' => 'Y',
					'template_map_inside' => 'N',
					'control' => 'N',
					'control_auto' => 'Y',
					'control_status_arrived' => '',
					'control_status_completed' => 'F',
					'control_status_completed_cod' => 'F',
					'browser' => 'ie',
					'register_status' => '',
					'sale_discount' => 'N',
					'sale_discount_cod' => 'off',
					'edost_discount' => 'Y',
					'template_ico' => 'C',
					'template_script' => 'Y',
					'package' => '1',
					'postmap' => 'Y',
					'office_near' => 'N',
					'office_unsupported' => 'N',
					'office_unsupported_fix' => '',
					'office_unsupported_percent' => '',
					'office_tel' => 'Y',
					'register_status_ignore' => '',
					'control_status_mode' => 'F',
					'control_status_update_ignore' => '',
					'register_no_paid_ignore' => 'N',
					'control_status_completed_cod_paid' => 'N',
					'param' => [
						'zero_tariff' => '104',
						'module_id' => '103'
					],
					'PAY_SYSTEM_ID' => '76',
					'COMPACT' => 'off',
					'PRIORITY' => 'P'
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
							'NAME' => 'Футболка Мужская огонь'
						]
					]
				]
			];
			//$office_get = [30, 23, 5];

			$ar = array();
			$ar[] = 'country=0';
			$ar[] = 'region=38';
			$ar[] = 'city='.urlencode('Иркутск');
			$ar[] = 'weight='.urlencode(0.1);
			$ar[] = 'insurance='.urlencode(2.85);
			$ar[] = 'size='.urlencode(implode('|', [0, 0, 0]));
			$r = \edost_class::RequestData('', 9481, 'Z9aJ9UTavqNzH8AtmhfzezozjqycvctB', implode('&', $ar), 'delivery');

			//$tariff = \CDeliveryEDOST::GetEdostTariff($r['data'][71]['']);
			$office_get = [$r['data'][71]['company_id']];

			$data = \edost_class::GetOffice($edost_order, $office_get);

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