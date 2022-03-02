<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const ESHOP_BOOTSTRAP = 'EshopBootstrap';
	public const ASPRO_MAX = 'AsproMax';
	public const ASPRO_NEXT = 'AsproNext';
	public const ASPRO_OPTIMUS = 'AsproOptimus';
	public const ASPRO_MSHOP = 'AsproMshop';

	public static function getTypes() : array
	{
		return [
			static::ESHOP_BOOTSTRAP,
			static::ASPRO_MAX,
			static::ASPRO_NEXT,
			static::ASPRO_OPTIMUS,
			static::ASPRO_MSHOP,
		];
	}

	public static function getInstance(string $type) : Skeleton
	{
		$className = static::makeClassName($type);

		Assert::isSubclassOf($className, Skeleton::class);

		return new $className();
	}

	protected static function makeClassName(string $type) : string
	{
		$type = str_replace('_', '', $type);

		return __NAMESPACE__ . '\\' . ucfirst($type);
	}
}