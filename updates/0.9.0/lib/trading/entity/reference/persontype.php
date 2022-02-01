<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

class PersonType
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @param string|null $siteId
	 *
	 * @return array{ID: string, VALUE: string}[]
	 */
	public function getEnum(string $siteId = null) : array
	{
		throw new Main\NotImplementedException('getEnum is missing');
	}

	/**
	 * @param string|null $siteId
	 *
	 * @return int|null
	 */
	public function getIndividualId(string $siteId = null) : ?int
	{
		throw new Main\NotImplementedException('getIndividualId is missing');
	}

	/**
	 * @param string|null $siteId
	 *
	 * @return int|null
	 */
	public function getLegalId(string $siteId = null) : ?int
	{
		throw new Main\NotImplementedException('getLegalId is missing');
	}
}