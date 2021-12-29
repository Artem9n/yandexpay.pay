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

	public function isSku(int $productId) : bool
	{
		throw new Main\NotImplementedException('isSku is missing');
	}

	public function searchOffers(int $productId, int $iblockId = 0, array $filter = []) : array
	{
		throw new Main\NotImplementedException('searchOffers is missing');
	}

	public function resolveOffer(int $productId) : int
	{
		throw new Main\NotImplementedException('resolveOffer is missing');
	}
}