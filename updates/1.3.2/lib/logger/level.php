<?php
namespace YandexPay\Pay\Logger;

use YandexPay\Pay\Psr;
use YandexPay\Pay\Reference\Concerns;

class Level extends Psr\Log\LogLevel
{
	use Concerns\HasMessage;

	protected static $variantsMap;
	protected static $variants = [
		self::EMERGENCY,
		self::ALERT,
		self::CRITICAL,
		self::ERROR,
		self::WARNING,
		self::NOTICE,
		self::INFO,
		self::DEBUG,
	];

	public static function getVariants() : array
	{
		return static::$variants;
	}

	public static function getEnum() : array
	{
		$result = [];

		foreach (static::getVariants() as $variant)
		{
			$result[$variant] = static::getTitle($variant);
		}

		return $result;
	}

	public static function isMatch(string $limit, string $target) : bool
	{
		$limitLevel = static::getVariantLevel($limit);
		$targetLevel = static::getVariantLevel($target);

		return (
			$limitLevel !== null
			&& $targetLevel !== null
			&& $limitLevel >= $targetLevel
		);
	}

	protected static function getVariantsMap() : array
	{
		if (static::$variantsMap === null)
		{
			static::$variantsMap = array_flip(static::$variants);
		}

		return static::$variantsMap;
	}

	protected static function getVariantLevel(string $level) : ?int
	{
		$map = static::getVariantsMap();

		return $map[$level] ?? null;
	}

	public static function getTitle(string $name) : string
	{
		return self::getMessage(sprintf('OPTION_NAME_%s', mb_strtoupper($name)), null, $name);
	}
}