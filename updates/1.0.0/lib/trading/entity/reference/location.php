<?php
namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main\NotImplementedException;

abstract class Location
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function getLocation(array $address) : ?int
	{
		throw new NotImplementedException('getLocationId is missing');
	}
}