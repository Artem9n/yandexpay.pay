<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Edost;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class Courier extends Base
{
	protected $format = ['door'];

	public function getServiceType() : string
	{
		return  EntitySale\Delivery::DELIVERY_TYPE;
	}

	public function markSelected(Sale\Order $order, string $storeId = null, string $address = null) : void
	{
		// do nothing
	}

	public function markSelectedDelivery(Sale\Order $order, array $address) : void
	{
		
	}
}