<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Boxberry;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Pvz extends Base
{
	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (
			!($service instanceof Sale\Delivery\Services\AutomaticProfile)
			&& !Main\Loader::includeModule('up.boxberrydelivery')
		) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return (mb_strpos($code, 'boxberry:PVZ') !== false);
	}

	protected function getType() : string
	{
		return Factory::BOXBERRY_PVZ;
	}
}