<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const ELEMENT = 'element';
	public const BASKET = 'basket';
	public const ORDER = 'order';

	public static function getTypes() : array
	{
		return [
			static::ELEMENT,
			static::BASKET,
			static::ORDER,
		];
	}

	public static function getInstance(string $type) : BehaviorInterface
	{
		$className = static::makeClassName($type);

		Assert::isSubclassOf($className, BehaviorInterface::class);

		return new $className();
	}

	protected static function makeClassName(string $type) : string
	{
		return __NAMESPACE__ . '\\' . ucfirst($type);
	}
}