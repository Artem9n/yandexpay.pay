<?php

namespace YandexPay\Pay\Trading\UseCase\Restrictions\ByPlatform;

use Bitrix\Main;
use Bitrix\Sale;

if (!Main\Loader::includeModule('sale') || !class_exists(Sale\Services\Base\Restriction::class)) { return; }

class PaySystem extends Sale\Services\Base\Restriction
{
	public static function getClassTitle() : string
	{
		return Rule::getClassTitle();
	}

	public static function getClassDescription() : string
	{
		return Rule::getClassDescription();
	}

	public static function isAvailable() : bool
	{
		return Rule::isAvailable();
	}

	public static function getParamsStructure($entityId = 0) : array
	{
		return Rule::getParamsStructure();
	}

	public static function check($params, array $restrictionParams, $serviceId = 0) : bool
	{
		return Rule::check($params, $restrictionParams);
	}

	protected static function extractParams(Sale\Internals\Entity $entity) : array
	{
		if (!($entity instanceof Sale\Payment)) { return []; }

		$collection = $entity->getCollection();

		if (!($collection instanceof Sale\PaymentCollection)) { return []; }

		$order = $collection->getOrder();

		if (!($order instanceof Sale\Order)) { return []; }

		return Rule::extractParams($order);
	}
}