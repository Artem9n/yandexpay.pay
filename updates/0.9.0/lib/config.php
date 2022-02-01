<?php

namespace YandexPay\Pay;

use Bitrix\Main\Config\Option;

class Config
{
	protected static $serializedOptionPrefix =  '__YANDEX__CONFIG__:';

	public static function getModuleName(): string
	{
		return 'yandexpay.pay';
	}

	public static function getLangPrefix(): string
	{
		return 'YANDEX_PAY_';
	}

	public static function getNamespace(): string
	{
		return '\\' . __NAMESPACE__;
	}

	public static function getModulePath(): string
	{
		return __DIR__;
	}

	public static function getModuleRelativePath(): string
	{
		return BX_ROOT . '/modules/' . static::getModuleName();
	}

	public static function getOption($name, $default = "", $siteId = false)
	{
		$moduleName = static::getModuleName();
		$optionValue = Option::get($moduleName, $name, null, $siteId);

		if (strpos($optionValue, static::$serializedOptionPrefix) === 0)
		{
			$unserializedValue = unserialize(substr($optionValue, strlen(static::$serializedOptionPrefix)), [false]);
			$optionValue = ($unserializedValue !== false ? $unserializedValue : null);
		}

		if (!isset($optionValue))
		{
			$optionValue = $default;
		}

		return $optionValue;
	}

	public static function setOption($name, $value = '', $siteId = ''): void
	{
		$moduleName = static::getModuleName();

		if (!is_scalar($value))
		{
			$value = static::$serializedOptionPrefix . serialize($value);
		}

		Option::set($moduleName, $name, $value, $siteId);
	}
}