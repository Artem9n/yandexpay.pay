<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Ozon;

use Ipol;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property Ipol\Ozon\Bitrix\Handler\DeliveryHandlerPickup $service */
class Delivery extends Base
{
	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Ipol\Ozon\Bitrix\Handler\DeliveryHandlerCourier)) { return false; }

		$code = $service->getCode();

		return $code === 'courier';
	}

	protected function getType() : string
	{
		return Factory::OZON_DELIVERY;
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::DELIVERY_TYPE;
	}
}