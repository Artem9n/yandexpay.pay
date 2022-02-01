<?php

namespace YandexPay\Pay\Gateway;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Internals\PaySystemActionTable;
use YandexPay\Pay\Config;
use YandexPay\Pay\Gateway\Payment\Best2pay;
use YandexPay\Pay\Reference\Assert;

class Manager
{
	public const PAYTURE = 'payture';
	public const BEST2PAY = 'best2pay';
	public const RBKMONEY = 'rbkmoney';
	public const RBS_ALFA = 'alfabank';
	public const RBS_MTS = 'mts';
	public const RBS_RSHB = 'rshb';
	public const RBS = 'rbs';

	public const OTHER = 'other';

	public static function getGatewayList() : array
	{
		return [
			static::PAYTURE,
			static::BEST2PAY,
			static::RBKMONEY,
			static::RBS_ALFA,
			static::RBS_MTS,
			static::RBS_RSHB,
			static::OTHER
		];
	}

	public static function getHandlerModeList(): array
	{
		$result = [];

		$classListGateway = static::getGatewayList();

		if (empty($classListGateway)) { return $result; }

		foreach ($classListGateway as $classGateway)
		{
			$gateWay = static::getProvider($classGateway);
			$result[$gateWay->getId()] = $gateWay->getName();
		}

		return $result;
	}

	public static function getProvider(string $type): Base
	{
		$className = '\\' . __NAMESPACE__ . '\\Payment\\' . ucfirst($type);

		Assert::classExists($className);
		Assert::isSubclassOf($className, Base::class);

		return new $className();
	}
}