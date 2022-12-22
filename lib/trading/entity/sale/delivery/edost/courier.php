<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Edost;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class Courier extends Base
{
	protected $code = 'edost:COURIER';

	public function getServiceType() : string
	{
		return  EntitySale\Delivery::DELIVERY_TYPE;
	}

	public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void
	{
		// do nothing
	}

	public function markSelectedDelivery(Sale\OrderBase $order, array $address) : void
	{
		/** @var Sale\Order $order */
		$this->calculateAndFillSessionValues($order);
	}
}