<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class UserGroup
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function getGroups() : array
	{
		throw new Main\NotImplementedException('getGroups is missing');
	}

	public function getDefaultGroup() : int
	{
		throw new Main\NotImplementedException('getDefaultGroup is missing');
	}
}