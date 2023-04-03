<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\RussianPost;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property \Sale\Handlers\Delivery\RussianpostProfile $service */
class Courier extends Base
{
	protected $accountId = 'courierId';
	protected $codeService = 'COURIER';

	public function serviceType() : string
	{
		return EntitySale\Delivery::COURIER_TYPE;
	}

	public function markSelectedCourier(Sale\Order $order, string $address, string $zip) : void
	{
		$this->fillTariff($order, $zip);
		$this->fillZip($order, $zip);
		$this->fillAddress($order, $address);
	}
}