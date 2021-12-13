<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup;

use Bitrix\Main;
use Bitrix\Sale;

class Factory
{
	public static function make(Sale\Delivery\Services\Base $service) : AbstractAdapter
	{
		if (static::testConfigurable($service))
		{
			$result = new Configurable($service);
		}
		else
		{
			throw new Main\ArgumentException(sprintf(
				'delivery service %s pickup not implemented',
				get_class($service)
			));
		}

		return $result;
	}

	protected static function testConfigurable(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\Configurable)) { return false; }

		$stores = Sale\Delivery\ExtraServices\Manager::getStoresList($service->getId());

		return !empty($stores); // todo if Choose pickup store option
	}
}