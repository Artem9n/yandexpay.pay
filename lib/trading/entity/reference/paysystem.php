<?php
/** @noinspection PhpUnusedParameterInspection */

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

class PaySystem
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function getEnum(string $siteId = null) : array
	{
		throw new Main\NotImplementedException('getEnum is missing');
	}

	public function getCompatible(Order $order, int $deliveryId = null) : array
	{
		throw new Main\NotImplementedException('getCompatible is missing');
	}
}