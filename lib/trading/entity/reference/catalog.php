<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main\NotImplementedException;

abstract class Catalog
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @param string $siteId
	 *
	 * @return int|null
	 */
	public function getIblock(string $siteId = null) : ?int
	{
		throw new NotImplementedException('getIblocks is missing');
	}

	/**
	 * @param string $siteId
	 *
	 * @return array
	 */
	public function getEnumIblock(string $siteId = null) : array
	{
		throw new NotImplementedException('getIblock is missing');
	}
}