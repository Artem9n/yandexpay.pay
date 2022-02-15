<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class Route
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function getPublicPath(string $urlId) : string
	{
		throw new Main\NotImplementedException('getPublicPath is missing');
	}

	public function installPublic(string $siteId) : void
	{
		throw new Main\NotImplementedException('installPublic is missing');
	}

	public function uninstallPublic(string $siteId) : void
	{
		throw new Main\NotImplementedException('uninstallPublic is missing');
	}
}