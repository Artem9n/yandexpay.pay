<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const ESHOP_BOOTSTRAP = 'EshopBootstrap';
	public const ASPRO = 'Aspro';
	public const ALTOP_ENEXT = 'AltopEnext';

	public static function getTypes() : array
	{
		return [
			static::ASPRO,
			static::ALTOP_ENEXT,
			static::ESHOP_BOOTSTRAP,
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
		$namespace = __NAMESPACE__;
		$formattedType = str_replace('_', '', $type);
		$formattedType = ucfirst($formattedType);

		return $namespace . '\\' . $formattedType;
	}
}