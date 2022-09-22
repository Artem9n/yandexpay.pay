<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;
use Bitrix\Sale;

abstract class Platform
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @return int|null
	 * @throws Main\NotImplementedException
	 */
	public function getId() : ?int
	{
		throw new Main\NotImplementedException('getId is missing');
	}

	/**
	 * @throws Main\NotImplementedException
	 */
	public function install() : void
	{
		throw new Main\NotImplementedException('install is missing');
	}

	/**
	 * @throws Main\NotImplementedException
	 */
	public function uninstall() : void
	{
		throw new Main\NotImplementedException('uninstall is missing');
	}

	/**
	 * @throws Main\NotImplementedException
	 */
	public function activate() : void
	{
		throw new Main\NotImplementedException('activate is missing');
	}

	/**
	 * @throws Main\NotImplementedException
	 */
	public function deactivate() : void
	{
		throw new Main\NotImplementedException('deactivate is missing');
	}

	/**
	 * @return Sale\TradingPlatform\Platform
	 * @throws Main\NotImplementedException
	 */
	public function getSalePlatform() : Sale\TradingPlatform\Platform
	{
		throw new Main\NotImplementedException('getSalePlatform is missing');
	}
}