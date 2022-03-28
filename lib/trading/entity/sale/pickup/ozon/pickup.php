<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup\Ozon;

use Ipol;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Pickup\Factory;

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