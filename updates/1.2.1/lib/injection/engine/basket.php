<?php
namespace YandexPay\Pay\Injection\Engine;

use YandexPay\Pay\Injection;
use YandexPay\Pay\Reference\Assert;

class Basket extends AbstractEngine
{
	public static function onEpilog(int $injectionId, array $settings) : void
	{
		if (!static::testRequest()) { return; }

		if (!isset($settings['PATH']) || !static::testUrl($settings['PATH'])) { return; }

		static::render($injectionId, ['SITE_ID' => $settings['SITE_ID']]);
	}
}