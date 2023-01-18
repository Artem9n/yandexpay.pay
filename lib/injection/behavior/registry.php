<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const ELEMENT = 'element';
	public const ELEMENT_FAST = 'elementFast';
	public const BASKET = 'basket';
	public const BASKET_FLY = 'basketFly';
	public const ORDER = 'order';

	public static function getTypes() : array
	{
		return [
			static::ELEMENT,
			static::ELEMENT_FAST,
			static::BASKET,
			static::BASKET_FLY,
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
		$namespace = __NAMESPACE__;
		$formattedName = ucfirst($type);

		return $namespace . '\\' . $formattedName;
	}
}