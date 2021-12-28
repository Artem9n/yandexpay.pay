<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class Product
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function getBasketData(array $productIds) : array
	{
		throw new Main\NotImplementedException('getBasketData is missing');
	}
}