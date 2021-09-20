<?php

namespace YandexPay\Pay;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

class Config
{
	protected static $serializedOptionPrefix =  '__YANDEX__CONFIG__:';

	public static function getModuleName(): string
	{
		return 'yandexpay.pay';
	}

	public static function getLang($code, $replaces = null, $fallback = null)
	{
		$prefix = static::getLangPrefix();

		$result = Loc::getMessage($prefix . $code, $replaces) ?: $fallback;

		if ($result === null)
		{
			$result = $code;
		}

		return $result;
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
			$unserializedValue = unserialize(substr($optionValue, strlen(static::$serializedOptionPrefix)), false);
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