<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Site\AvailableStore;

use YandexPay\Pay\Reference\Assert;

class Strategy
{
	public const ALL = 'all';

	public static function types() : array
	{
		return [
			static::ALL,
		];
	}

	public static function getInstance(string $type) : InterfaceStrategy
	{
		$className = static::makeClassName($type);

		Assert::isSubclassOf($className, InterfaceStrategy::class);

		return new $className();
	}

	protected static function makeClassName(string $type) : string
	{
		$namespace = __NAMESPACE__;
		$formattedName = ucfirst($type);

		return $namespace . '\\' . $formattedName;
	}
}