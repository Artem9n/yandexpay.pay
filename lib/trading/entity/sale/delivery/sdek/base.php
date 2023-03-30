<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Sdek;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Base extends AbstractAdapter
{
	protected $title;
	protected $codeService;
	protected $tariff;

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getNameWithParent();

		return $code === $this->codeService;
	}

	public function load() : bool
	{
		return Main\Loader::includeModule('ipol.sdek');
	}

	public function providerType() : ?string
	{
		return 'CDEK';
	}

	protected function addressCode(Sale\Order $order) : string
	{
		return '';
	}

	protected function zipCode(Sale\Order $order) : string
	{
		return '';
	}

	protected function fillTariff(Sale\Order $order) : void
	{
		$shipmentCollection = $order->getShipmentCollection();

		/** @var Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem()) { continue; }

			$shipment->calculateDelivery();
		}

		$tariff = $_SESSION['IPOLSDEK_CHOSEN'][$this->tariff];

		/** @var Sale\PropertyValue $property */
		foreach ($order->getPropertyCollection() as $property)
		{
			if ($property->getField('CODE') !== 'IPOLSDEK_CNTDTARIF') { continue; }

			$property->setValue($tariff);
		}
	}
}