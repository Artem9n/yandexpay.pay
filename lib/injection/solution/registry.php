<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const ESHOP_BOOTSTRAP = 'EshopBootstrap';
	public const ASPRO = 'Aspro';
	public const NEXTYPE_MAGNET= 'NextypeMagnet';

	public static function getTypes() : array
	{
		return [
			static::ASPRO,
			static::ESHOP_BOOTSTRAP,
			static::NEXTYPE_MAGNET,
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