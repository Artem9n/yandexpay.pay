<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup\Sdek;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Pickup\Factory;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Pickup extends Base
{
	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return $code === 'sdek:pickup';
	}

	protected function getType() : string
	{
		return Factory::SDEK_PICKUP;
	}
}