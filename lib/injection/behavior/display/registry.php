<?php
namespace YandexPay\Pay\Injection\Behavior\Display;

use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const BUTTON = 'Button';
	public const WIDGET = 'Widget';

	public static function getTypes() : array
	{
		return [
			static::BUTTON,
			static::WIDGET,
		];
	}

	public static function create(string $type) : IDisplay
	{
		$className = static::makeClassName($type);

		Assert::isSubclassOf($className, IDisplay::class);

		return new $className();
	}

	protected static function makeClassName(string $type) : string
	{
		$namespace = __NAMESPACE__;
		$formattedType = ucfirst($type);

		return $namespace . '\\' . $formattedType;
	}
}