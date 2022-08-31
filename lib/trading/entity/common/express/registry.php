<?php
namespace YandexPay\Pay\Trading\Entity\Common\Express;

use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const NEAREST = 'nearest';

	public static function types() : array
	{
		return [
			static::NEAREST,
		];
	}

	public static function make(string $type) : AbstractStrategy
	{
		$className = __NAMESPACE__ . '\\' . ucfirst($type) . 'Strategy';

		Assert::isSubclassOf($className, AbstractStrategy::class);

		return new $className();
	}
}