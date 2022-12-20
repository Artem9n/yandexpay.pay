<?php
namespace YandexPay\Pay\Ui\Update\Check;

use YandexPay\Pay;

class Event extends Pay\Reference\Event\Base
{
	public static function install() : void
	{
		foreach (static::getHandlers() as $handler)
		{
			static::register($handler);
		}
	}

	public static function uninstall() : void
	{
		foreach (static::getHandlers() as $handler)
		{
			static::unregister($handler);
		}
	}

	public static function getHandlers() : array
	{
		return [
			[
				'module' => 'main',
				'event' => 'OnModuleUpdate',
			],
		];
	}

	public static function OnModuleUpdate($readyModules) : void
	{
		if (is_array($readyModules) && in_array(Pay\Config::getModuleName(), $readyModules, true))
		{
			\CAdminNotify::DeleteByTag(Agent::NOTIFY_TAG);

			static::uninstall();
		}
	}
}
