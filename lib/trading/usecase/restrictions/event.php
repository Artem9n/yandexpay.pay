<?php

namespace YandexPay\Pay\Trading\UseCase\Restrictions;

use YandexPay\Pay as YandexPay;
use Bitrix\Main;

class Event extends YandexPay\Reference\Event\Regular
{
	public static function getHandlers() : array
	{
		return [
			[
				'module' => 'sale',
				'event' => 'onSaleDeliveryRestrictionsClassNamesBuildList',
				'method' => 'onDeliveryBuildList',
			],
			[
				'module' => 'sale',
				'event' => 'onSalePaySystemRestrictionsClassNamesBuildList',
				'method' => 'onPaySystemBuildList',
			],
		];
	}

	public static function onDeliveryBuildList() : Main\EventResult
	{
		return new Main\EventResult(
			Main\EventResult::SUCCESS,
			[
				ByPlatform\Delivery::class => static::getRestrictionsDir() . '/delivery.php'
			]
		);
	}

	public static function onPaySystemBuildList() : Main\EventResult
	{
		return new Main\EventResult(
			Main\EventResult::SUCCESS,
			[
				ByPlatform\PaySystem::class => static::getRestrictionsDir() . '/paysystem.php'
			]
		);
	}

	private static function getRestrictionsDir() : string
	{
		$moduleRoot = BX_ROOT . '/modules/' . YandexPay\Config::getModuleName();

		return $moduleRoot . '/lib/trading/usecase/restrictions/byplatform';
	}
}