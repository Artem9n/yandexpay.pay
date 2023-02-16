<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main\Context;
use YandexPay\Pay\Injection;
use YandexPay\Pay\Reference\Assert;

class Order extends AbstractEngine
{
	public static function onEpilog(int $injectionId, array $settings) : void
	{
		if (!static::testShow($settings)) { return; }

		if (!isset($settings['PATH']) || !static::testUrl($settings['PATH'])) { return; }

		[ $componentParameters ] = static::getRenderParameters($injectionId, ['SITE_ID' => $settings['SITE_ID']]);

		if (!static::testRender($componentParameters)) { return; }

		static::render($componentParameters);
	}

	protected static function getUrlVariants() : array
	{
		$variants = parent::getUrlVariants();

		foreach ($variants as $url)
		{
			if (static::isOrderId($url))
			{
				return [];
			}
		}

		return $variants;
	}

	protected static function isOrderId(string $url) : bool
	{
		return mb_strpos($url, 'ORDER_ID') !== false;
	}
}