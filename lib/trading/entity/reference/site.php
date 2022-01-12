<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class Site
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @return string[]
	 */
    public function getVariants() : array
    {
    	throw new Main\NotImplementedException('getVariants is missing');
    }

	/**
	 * @param string $siteId
	 *
	 * @return string
	 */
	public function getTitle(string $siteId) : string
    {
	    throw new Main\NotImplementedException('getTitle is missing');
    }

	public function getOptions() : array
	{
		throw new Main\NotImplementedException('getOptions is missing');
	}

	public function getDefault() : string
	{
		throw new Main\NotImplementedException('getDefault is missing');
	}
}