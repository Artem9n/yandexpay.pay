<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup\Boxberry;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Pickup\Factory;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Pvz extends Base
{
	protected $prepaid = 1;

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return $code === 'boxberry:PVZ';
	}

	protected function getType() : string
	{
		return Factory::BOXBERRY_PVZ;
	}
}