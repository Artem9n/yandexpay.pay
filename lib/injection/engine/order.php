<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main\Context;
use YandexPay\Pay\Reference\Event;
use YandexPay\Pay\Injection;

class Order extends Event\Base
{
	public static function onEpilog(int $injectionId, array $settings) : void
	{
		self::render($injectionId, $settings);
	}

	protected static function render(int $injectionId, array $settings) : void
	{
		if (self::$handlerDisallowYaPay) { return; }

		global $APPLICATION;

		$model = Injection\Setup\Model::wakeUp(['ID' => $injectionId]);

		if (!static::isOrderPath($settings['PATH_ORDER'])) { return; }

		if (trim($model->getSelectorValue()) === '') { return; }

		$APPLICATION->IncludeComponent('yandexpay.pay:trading.cart', '', [
			'INJECTION_ID'	=> $injectionId,
			'MODE'			=> Injection\Behavior\Registry::ORDER
		], false);

		self::$handlerDisallowYaPay = true;
	}

	protected static function getUrl() : string
	{
		return Context::getCurrent()->getRequest()->getRequestUri();
	}

	protected static function isOrderPath(string $basketPath) : bool
	{
		$result = false;

		$url = static::getUrl();

		if (static::isOrderId($url)) { return false; }

		if ($url === $basketPath) { return true; }

		$url = static::normalize($url);

		if ($basketPath === $url) { return true; }

		return $result;
	}

	protected static function normalize($path) : string
	{
		$symbolPos = mb_strpos($path, '?');

		if ($symbolPos !== false)
		{
			$path = mb_substr($path, 0, $symbolPos);
		}

		return $path;
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