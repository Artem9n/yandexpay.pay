<?php
namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main\NotImplementedException;

abstract class Environment
{
	public function getOrderRegistry() : OrderRegistry
	{
		throw new NotImplementedException('getOrderRegistry is missing');
	}

	public function getLocation() : Location
	{
		throw new NotImplementedException('getLocation is missing');
	}

	public function getDelivery() : Delivery
	{
		throw new NotImplementedException('getDelivery is missing');
	}
}