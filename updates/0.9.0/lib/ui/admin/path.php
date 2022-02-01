<?php

namespace YandexPay\Pay\Ui\Admin;

use YandexPay\Pay\Config;

class Path
{
	public const MODULE_PATH_PREFIX = 'yapay_';

	public static function getModuleUrl($scriptName, $query = null) : string
	{
		$fullScriptName = static::MODULE_PATH_PREFIX . $scriptName;

		return static::getPageUrl($fullScriptName, $query);
	}

	public static function getPageUrl($scriptName, $query = null) : string
	{
		$scriptName = mb_strtolower($scriptName);
		$path = BX_ROOT . '/admin/' . $scriptName . '.php';

		if ($query !== null)
		{
			$path .= '?' . http_build_query($query);
		}

		return $path;
	}

	public static function getToolsUrl($scriptPath, $query = null) : string
	{
		$scriptPath = mb_strtolower($scriptPath);
		$path = BX_ROOT . '/tools/' . Config::getModuleName() . '/' . $scriptPath . '.php';

		if ($query !== null)
		{
			$path .= '?' . http_build_query($query);
		}

		return $path;
	}
}