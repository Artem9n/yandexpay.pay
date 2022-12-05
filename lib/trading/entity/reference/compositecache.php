<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class CompositeCache
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function clearCache(string $domain) : void
	{
		throw new Main\NotImplementedException('clearCache is missing');
	}
}