<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Sdek;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Postamat extends Base
{
	protected $code = 'POSTAMAT';

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return $code === 'sdek:postamat';
	}

	protected function getType() : string
	{
		return Factory::SDEK_POSTAMAT;
	}
}