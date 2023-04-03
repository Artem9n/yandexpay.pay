<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Edost;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class Courier extends Base
{
	protected $format = ['door'];

	public function serviceType() : string
	{
		return  EntitySale\Delivery::COURIER_TYPE;
	}
}