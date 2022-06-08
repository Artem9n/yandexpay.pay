<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Boxberry;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class PvzCode extends Base
{
	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return $code === 'boxberry:PVZ_COD';
	}

	protected function getType() : string
	{
		return Factory::BOXBERRY_PVZ_COD;
	}
}