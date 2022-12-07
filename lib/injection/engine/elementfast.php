<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main;
use YandexPay\Pay\Injection;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Entity as TradingEntity;

class ElementFast extends Element
{
	public static function onEndBufferContent(int $injectionId, array $settings, string &$content) : void
	{
		if (!static::testRequest() || !static::testQuery($settings)) { return; }

		$elementId = static::findProduct($settings);

		if ($elementId === null) { return; }

		$content = static::render($injectionId, ['PRODUCT_ID' => $elementId], static::RENDER_RETURN);

		//$content .= static::render($injectionId, ['PRODUCT_ID' => $elementId], static::RENDER_RETURN);
	}

	protected static function testQuery(array $context = []) : bool
	{
		return static::getRequestUrl($context) !== '';
	}

	protected static function getRequestUrl(array $settings = []) : string
	{
		$queryParams = $settings['QUERY'] ?? '';
		return static::getRequest()->getQuery($queryParams) ?? '';
	}
}