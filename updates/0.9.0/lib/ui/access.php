<?php
namespace YandexPay\Pay\Ui;

use YandexPay\Pay;

class Access
{
	public const RIGHTS_READ = 'R';
	public const RIGHTS_WRITE = 'W';

	public static function isReadAllowed() : bool
	{
		return static::hasRights(static::RIGHTS_READ);
	}

	public static function isWriteAllowed() : bool
	{
		return static::hasRights(static::RIGHTS_WRITE);
	}

	public static function hasRights(string $level) : bool
	{
		return (static::getRights() >= $level);
	}

	protected static function getRights()
	{
		$moduleId = Pay\Config::getModuleName();

		return \CMain::GetUserRight($moduleId);
	}
}