<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Boxberry;

use Bitrix\Sale;
use Bitrix\Main;
use YandexPay\Pay\Trading\Entity\Sale\Delivery;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Base extends Delivery\AbstractAdapter
{
	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return mb_strpos($code, $this->codeService) !== false;
	}

	public function load() : bool
	{
		return Main\Loader::includeModule('up.boxberrydelivery');
	}

	protected function zipCode(Sale\Order $order) : string
	{
		return Main\Config\Option::get(\CBoxberry::$moduleId, 'BB_ZIP');
	}

	protected function addressCode(Sale\Order $order) : string
	{
		return Main\Config\Option::get(\CBoxberry::$moduleId, 'BB_ADDRESS');
	}

	public function providerType() : ?string
	{
		return 'BOXBERRY';
	}
}