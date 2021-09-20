<?php

namespace YandexPay\Pay\Reference;

use Bitrix\Main;

class Assert
{
	public static function notNull($value, $argument, $message = null): void
	{
		if ($value === null)
		{
			$message = $message ?? sprintf('Argument "%s" is null', $argument);

			throw new Main\ArgumentException($message, $argument);
		}
	}

	public static function typeOf($value, $className, $argument): void
	{
		if (!($value instanceof $className))
		{
			throw new Main\ArgumentTypeException($argument, $className);
		}
	}

	public static function isArray($value, $argument): void
	{
		if (!is_array($value))
		{
			throw new Main\ArgumentTypeException($argument, 'Array');
		}
	}

	public static function classExists($className): void
	{
		if (!class_exists($className))
		{
			throw new Main\NotImplementedException(sprintf('class %s not exists', $className));
		}
	}

	public static function isSubclassOf($className, $parentName): void
	{
		if (!is_subclass_of($className, $parentName))
		{
			throw new Main\InvalidOperationException(sprintf(
				'%s must extends %s',
				$className,
				$parentName
			));
		}
	}
}