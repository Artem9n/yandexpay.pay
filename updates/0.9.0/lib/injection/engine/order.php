<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main\Context;
use YandexPay\Pay\Injection;
use YandexPay\Pay\Reference\Assert;

class Order extends AbstractEngine
{
	public static function onEpilog(int $injectionId, array $settings) : void
	{
		if (!static::testRequest()) { return; }

		if (!isset($settings['PATH']) || !static::isOrderPath($settings['PATH'])) { return; }

		static::render($injectionId);
	}

	protected static function isOrderPath(string $path) : bool
	{
		$url = static::getUrl();

		if (static::isOrderId($url)) { return false; }

		if ($url === $path) { return true; }

		$url = static::normalize($url);

		return $path === $url;
	}

	protected static function isOrderId(string $url) : bool
	{
		$result = false;

		if (mb_strpos($url, 'ORDER_ID') !== false)
		{
			$result = true;
		}

		return $result;
	}
}