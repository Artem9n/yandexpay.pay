<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Sdek;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Courier extends Base
{
	protected $codeService = 'sdek:courier';
	protected $tariff = 'courier';

	public function serviceType() : string
	{
		return EntitySale\Delivery::COURIER_TYPE;
	}

	public function markSelectedCourier(Sale\Order $order, string $address, string $zip) : void
	{
		$this->fillTariff($order);
		$this->fillAddress($order, $address);
	}

	protected function addressCode(Sale\Order $order) : string
	{
		return (string)\Ipolh\SDEK\option::get('address');
	}
}