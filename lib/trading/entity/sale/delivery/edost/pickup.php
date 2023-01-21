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

	protected $format = 'office';

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}

	public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void
	{
		$propAddress = $this->addressProperty($order);

		if ($propAddress !== null)
		{
			$propAddress->setValue($address);
		}
	}

	public function getStores(Sale\OrderBase $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		if ($bounds === null) { return []; }

		/** @var Sale\Order $order */
		$config = $this->config($order);
		$profile = \CDeliveryEDOST::GetEdostProfile($service->getId());
		$tariff = \CDeliveryEDOST::GetEdostTariff($profile['profile']);
		$providerType = $this->getProvider();
		$deliveryId = $service->getId();

		$bitrix_data[$deliveryId] = [
			'ID' => $deliveryId,
			'OWN_NAME' => $service->getName(),
			'DESCRIPTION' => $service->getDescription(),
			'SORT' => $service->getSort(),
			'CODE' => $service->getCode(),
			'CURRENCY' => $service->getCurrency(),
		];

		$result = [];

		foreach ($this->locations($bounds) as $locationCode)
		{
			$deliveryLocationProperty = $order->getPropertyCollection()->getDeliveryLocation();

			if ($deliveryLocationProperty === null) { return $result; }

			$deliveryLocationProperty->setValue($locationCode);
			$shipment = $this->orderShipment($order, $service);
			$deliveryPrice = $shipment->calculateDelivery()->getDeliveryPrice();

			$formatTariff = \edost_class::FormatTariff(
				$bitrix_data,
				$order->getCurrency(),
				[ 'bitrix' => $order ],
				[ 'id' => $deliveryId ],
				$config
			);

			$locationId = \CSaleLocation::getLocationIDbyCODE($locationCode);
			$result[$locationId] = [];

			if (empty($formatTariff['office'])) { return  $result; }

			foreach ($formatTariff['office'] as $company)
			{
				foreach ($company as $pickup)
				{
					$pickup['amount'] = $deliveryPrice;
					$pickup['provider'] = $providerType;
					$pickup['address'] = \edost_class::GetOfficeAddress($pickup, $tariff);
					$result[$locationId][] = $this->makePickupInfo($pickup);
				}
			}
		}

		return $result;
	}

	protected function locations(array $bounds) : array
	{
		$metadata = new Data\Location\MetaData();
		$finder = new Data\Location\Bounds($metadata);

		return array_keys($finder->search(
			$bounds['sw']['latitude'],
			$bounds['sw']['longitude'],
			$bounds['ne']['latitude'],
			$bounds['ne']['longitude'])
		);
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

	private function makePickupInfo($pickup) : array
	{
		$coords = explode(',', $pickup['gps']);

		return [
			'ID' => $pickup['code'],
			'ADDRESS' => $pickup['address'],
			'TITLE' => sprintf('(%s) %s', $this->title, $pickup['name'] ?: $pickup['code']),
			'GPS_N' => $coords[1],
			'GPS_S' => $coords[0],
			'PHONE' => $pickup['tel'] ?? '',
			'AMOUNT' => $pickup['amount'],
			'DESCRIPTION' => $pickup['schedule'],
			'PROVIDER' => $pickup['provider'],
		];
	}
}