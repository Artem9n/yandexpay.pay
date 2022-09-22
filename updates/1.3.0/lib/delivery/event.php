<?php

namespace YandexPay\Pay\Delivery;

use Bitrix\Main;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference;

class Event extends Reference\Event\Regular
{
	public static function getHandlers() : array
	{
		return [
			[
				'module' => 'sale',
				'event' => 'onSaleDeliveryHandlersClassNamesBuildList'
			]
		];
	}

	public static function onSaleDeliveryHandlersClassNamesBuildList(Main\Event $event) : Main\EventResult
	{
		return new Main\EventResult(Main\EventResult::SUCCESS, [
			Yandex\Handler::class => BX_ROOT . '/modules/' . Config::getModuleName() . '/lib/delivery/yandex/handler.php'
		]);
	}
}
