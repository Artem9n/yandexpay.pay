<?php

namespace YandexPay\Pay\Reference\Concerns;

use YandexPay\Pay;

trait HasOnceStatic
{
	private static $onceMemoizedStatic = [];

	protected static function onceStatic($name, $arguments = null)
	{
		$cacheKey = $name . ':' . Pay\Utils\Caller::getArgumentsHash($arguments);

		if (!isset(self::$onceMemoizedStatic[$cacheKey]) && !array_key_exists($cacheKey, self::$onceMemoizedStatic))
		{
			self::$onceMemoizedStatic[$cacheKey] = static::callOnceStatic($name, $arguments);
		}

		return self::$onceMemoizedStatic[$cacheKey];
	}

	private static function callOnceStatic($name, $arguments)
	{
		if ($arguments === null)
		{
			$result = static::{$name}();
		}
		else if (is_array($arguments))
		{
			$result = static::{$name}(...$arguments);
		}
		else
		{
			$result = static::{$name}($arguments);
		}

		return $result;
	}
}