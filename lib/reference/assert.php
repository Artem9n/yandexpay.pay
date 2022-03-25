<?php

namespace YandexPay\Pay\Reference;

use Bitrix\Main;

class Assert
{
	public static function notNull($value, $argument, $message = null, $exceptionClassName = Main\ArgumentException::class): void
	{
		if ($value === null)
		{
			$message = $message ?? sprintf('Argument "%s" is null', $argument);

			throw new $exceptionClassName($message, $argument);
		}
	}

	public static function typeOf($value, $className, $argument, $exceptionClassName = Main\ArgumentTypeException::class): void
	{
		if (!($value instanceof $className))
		{
			throw new $exceptionClassName($argument, $className);
		}
	}

	public static function isArray($value, $argument, $exceptionClassName = Main\ArgumentTypeException::class): void
	{
		if (!is_array($value))
		{
			throw new $exceptionClassName($argument, 'Array');
		}
	}

	public static function classExists($className, $exceptionClassName = Main\NotImplementedException::class): void
	{
		if (!class_exists($className))
		{
			throw new $exceptionClassName(sprintf('class %s not exists', $className));
		}
	}

	public static function isString($value, $argument, $exceptionClassName = Main\ArgumentException::class): void
	{
		if (!is_string($value))
		{
			throw new $exceptionClassName($argument, 'String');
		}
	}

	public static function isNumber($value, $argument, $exceptionClassName = Main\ArgumentException::class): void
	{
		if (!is_numeric($value))
		{
			throw new $exceptionClassName($argument, 'Number');
		}
	}

	public static function isSubclassOf($className, $parentName, $exceptionClassName = Main\InvalidOperationException::class): void
	{
		if (!is_subclass_of($className, $parentName))
		{
			throw new $exceptionClassName(sprintf(
				'%s must extends %s',
				$className,
				$parentName
			));
		}
	}

	public static function methodExists($classOrObject, $method, $exceptionClassName = Main\InvalidOperationException::class): void
	{
		if (!method_exists($classOrObject, $method))
		{
			throw new $exceptionClassName(sprintf(
				'%s missing method %s',
				is_object($classOrObject) ? get_class($classOrObject) : $classOrObject,
				$method
			));
		}
	}
}