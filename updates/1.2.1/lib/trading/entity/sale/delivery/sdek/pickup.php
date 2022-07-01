<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Sdek;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Pickup extends Base
{
	protected $code = 'PVZ';

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getNameWithParent();

		return $code === 'sdek:pickup';
	}

	protected function getType() : string
	{
		return Factory::SDEK_PICKUP;
	}
}