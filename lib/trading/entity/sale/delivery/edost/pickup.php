<?php
namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Edost;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Data;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class Pickup extends Base
{
	use Concerns\HasOnceStatic;

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
		if ($bounds === null) { return []; }

		/** @var Sale\Order $order */
		$config = $this->config($order);
		$result = [];

		foreach ($this->locations($bounds) as $locationCode)
		{
			$edostLocation = \CDeliveryEDOST::GetEdostLocation($locationCode);

			if (empty($edostLocation)) { continue; }

			$companies = $this->companies($order, $edostLocation, $config);
			$company = $this->deliveryCompany($companies, $service);
			$companyId = $company['company_id'];
			$shipment = $this->orderShipment($order, $service);
			$points = $this->points($shipment, $locationCode, $config, $companies);

			if (!isset($points['data'][$companyId]) || !is_array($points['data'][$companyId])) { continue; }

			$locationId = \CSaleLocation::getLocationIDbyCODE($locationCode);
			$result[$locationId] = [];

			foreach ($points['data'][$companyId] as $pickup)
			{
				$result[$locationId][] = $this->makePickupInfo($pickup);
			}
		}

		return $result;
	}

	protected function locations(array $bounds) : array
	{
		$metadata = new Data\Location\MetaData();
		$finder = new Data\Location\Bounds($metadata);

		return array_keys($finder->search($bounds));
	}

	protected function orderShipment(Sale\OrderBase $order, Sale\Delivery\Services\Base $service) : Sale\Shipment
	{
		/** @var Sale\Order $order */
		/** @var Sale\BasketItem $basketItem */
		/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->getNotSystemItems()->current();

		if ($shipment === null)
		{
			$shipment = $shipmentCollection->createItem();
			$shipmentItemCollection = $shipment->getShipmentItemCollection();

			foreach ($order->getBasket() as $basketItem)
			{
				$shipmentItem = $shipmentItemCollection->createItem($basketItem);

				if ($shipmentItem)
				{
					$shipmentItem->setQuantity($basketItem->getQuantity());
				}
			}
		}

		$shipment->setField('DELIVERY_ID', $service->getId());
		$shipment->setField('DELIVERY_NAME', $service->getNameWithParent());

		return $shipment;
	}

	protected function config(Sale\OrderBase $order) : array
	{
		$result = \CDeliveryEDOST::GetEdostConfig($order->getSiteId());

		if (empty($result['id']))
		{
			throw new Main\SystemException('cant load config');
		}

		if (empty($result['ps']))
		{
			throw new Main\SystemException('empty config ps');
		}

		return $result;
	}

	protected function companies(Sale\OrderBase $order, array $edostLocation, array $config)
	{
		$parameters = array_map('urlencode', [
			'country' => $edostLocation['country'],
			'region' => $edostLocation['region'],
			'city' => $edostLocation['city'],
			'weight' => $order->getBasket()->getWeight() / 1000,
			'insurance' => $order->getBasket()->getPrice(),
			'size' => urlencode(implode('|', $this->getDimensions($order))),
		]);
		$parametersString = implode('&', array_map(
			static function($key, $value) { return $key . '=' . $value; },
			array_keys($parameters),
			$parameters
		));

		return static::onceStatic('fetchCompanies', [
			array_intersect_key($config, [
				'host' => true,
				'id' => true,
				'ps' => true,
			]),
			$parametersString,
		]);
	}

	/** @noinspection PhpUnused */
	protected static function fetchCompanies(array $config, string $parametersString) : array
	{
		$response = \edost_class::RequestData(
			$config['host'],
			$config['id'],
			$config['ps'],
			$parametersString,
			'delivery'
		);

		if (!isset($response['data']))
		{
			throw new Main\SystemException('cant fetch companies');
		}

		return $response['data'];
	}

	protected function deliveryCompany(array $companies, Sale\Delivery\Services\Base $service) : array
	{
		[, $profileId] = explode(':', $service->getCode(), 2);
		$company = $companies[$profileId] ?? null;

		if (empty($company))
		{
			throw new Main\SystemException('not found company for delivery service');
		}

		if (!is_array($company))
		{
			throw new Main\SystemException('invalid company for delivery service');
		}

		return $company;
	}

	protected function points(Sale\Shipment $shipment, string $locationCode, array $config, array $companies) : array
	{
		$shipment->getOrder()->getPropertyCollection()->getDeliveryLocation()->setValue($locationCode);

		$dimensions = $this->getDimensions($shipment->getOrder());
		$orderData = \CSaleDelivery::convertOrderNewToOld($shipment);
		$orderDataOriginal = \CDeliveryEDOST::FilterOrder($orderData);

		$edostOrderData = [
			'location' => \CDeliveryEDOST::GetEdostLocation($locationCode),
			'zip' => '',
			'weight' => $this->shipmentWeight($shipment) / 1000,
			'price' => $this->shipmentPrice($shipment),
			'sizesum' => 0, //array_sum($dimensions),
			'size1' => 0, //array_shift($dimensions),
			'size2' => 0, //array_shift($dimensions),
			'size3' => 0, //array_shift($dimensions),
			'config' => $config + [
				'PAY_SYSTEM_ID' => '76',
				'COMPACT' => 'off',
				'PRIORITY' => 'P',
			],
			'original' => $orderDataOriginal,
		];

		return \edost_class::GetOffice($edostOrderData, array_column($companies, 'company_id'));
	}

	protected function shipmentWeight(Sale\Shipment $shipment) : float
	{
		/** @var Sale\ShipmentItem $shipmentItem */
		$result = 0.0;

		foreach ($shipment->getShipmentItemCollection() as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();

			$result += $basketItem->getWeight() * $basketItem->getQuantity();
		}

		return $result;
	}

	protected function shipmentPrice(Sale\Shipment $shipment) : float
	{
		/** @var Sale\ShipmentItem $shipmentItem */
		$result = 0.0;

		foreach ($shipment->getShipmentItemCollection() as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();

			$result += $basketItem->getPrice() * $basketItem->getQuantity();
		}

		return $result;
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
					$dimensions = unserialize($dimensions, [ 'allowed_classes' => false ]);

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

		return [
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
	}
}