<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class OrderRegistry
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function createOrder($siteId, $userId, $currency) : Order
	{
		throw new Main\NotImplementedException('createOrder is missing');
	}
}