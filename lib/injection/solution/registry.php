<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const ESHOP_BOOTSTRAP = 'EshopBootstrap';
	public const ALTOP_ENEXT = 'AltopEnext';
	public const ASPRO_DEF = 'Aspro.Base';
	public const ASPRO_MAX = 'Aspro.Max';
	public const ASPRO_LITE = 'Aspro.Lite';
	public const DELUXE = 'Deluxe';
	public const NEXTYPE_MAGNET= 'NextypeMagnet';

	public static function getTypes() : array
	{
		return [
			static::NEXTYPE_MAGNET,
			static::DELUXE,
			static::ALTOP_ENEXT,
			static::ASPRO_MAX,
			static::ASPRO_LITE,
			static::ASPRO_DEF,
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

		[$namespaceClass, $className] = explode('.', $formattedType, 2);

		if ($className === null)
		{
			return $namespace . '\\' . $formattedType;
		}
		else
		{
			return $namespace . '\\' . ucfirst($namespaceClass) . '\\' . ucfirst($className);
		}
	}
}