<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Boxberry;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery as EntityDelivery;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Courier extends Base
{
	protected $codeService = 'boxberry:KD_COD';

	public function serviceType() : string
	{
		return EntityDelivery::COURIER_TYPE;
	}

	public function markSelectedCourier(Sale\Order $order, string $address, string $zip) : void
	{
		$this->fillAddress($order, $address);
		$this->fillZip($order, $zip);
	}
}