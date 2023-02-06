<?php
namespace YandexPay\Pay\Injection\Engine;

use YandexPay\Pay\Injection;
use YandexPay\Pay\Reference\Assert;

class Basket extends AbstractEngine
{
	public static function onEpilog(int $injectionId, array $settings) : void
	{
		if (!static::testShow($settings)) { return; }

		if (!isset($settings['PATH']) || !static::testUrl($settings['PATH'])) { return; }

		[ $componentParameters ] = static::getRenderParameters($injectionId, ['SITE_ID' => $settings['SITE_ID']]);

		static::render($componentParameters);
	}
}