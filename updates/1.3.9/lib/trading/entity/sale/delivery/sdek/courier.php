<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Sdek;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Courier extends Base
{
	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getNameWithParent();

		return $code === 'sdek:courier';
	}

	protected function getType() : string
	{
		return Factory::SDEK_COURIER;
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::DELIVERY_TYPE;
	}

	public function markSelectedDelivery(Sale\Order $order, array $address) : void
	{
		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $order->getShipmentCollection();

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem()) { continue; }

			$shipment->calculateDelivery();
		}

		$tariff = $_SESSION['IPOLSDEK_CHOSEN']['courier'];

		/** @var \Bitrix\Sale\PropertyValue $property */
		foreach ($order->getPropertyCollection() as $property)
		{
			if ($property->getField('CODE') !== 'IPOLSDEK_CNTDTARIF') { continue; }

			$property->setValue($tariff);
		}
	}
}