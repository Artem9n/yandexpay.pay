<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Ozon;

use Ipol;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;

/** @property Ipol\Ozon\Bitrix\Handler\DeliveryHandlerPickup $service */
class Pickup extends Base
{
	protected $typeVariant = 'pickup';

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Ipol\Ozon\Bitrix\Handler\DeliveryHandlerPickup)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return $code === 'pickup';
	}

	protected function getType() : string
	{
		return Factory::OZON_PICKUP;
	}
}