<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Dpd;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class Courier extends Base
{
	protected $code = 'ipolh_dpd:COURIER';

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
		/** @var Sale\Order $order */
		$this->calculateAndFillSessionValues($order);
	}
}