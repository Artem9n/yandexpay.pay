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

	public function load(string $paymentNumber) : Order
	{
		throw new Main\NotImplementedException('load is missing');
	}

	public function loadOrder(int $orderId) : Order
	{
		throw new Main\NotImplementedException('loadOrder is missing');
	}

	public function searchOrder(Platform $platform, string $externalId) : ?int
	{
		throw new Main\NotImplementedException('searchOrder is missing');
	}
}