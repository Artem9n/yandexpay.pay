<?php
namespace YandexPay\Pay\Injection\Engine;

use YandexPay\Pay\Injection;

class BasketFly extends AbstractEngine
{
	protected static $widgetContent;

	public static function onProlog(int $injectionId, array $settings) : void
	{
		if (!static::testRequest()) { return; }

		if (!isset($settings['PATH']) || !static::testUrl($settings['PATH'])) { return; }

		static::$widgetContent = static::render($injectionId, ['SITE_ID' => $settings['SITE_ID']], $settings['RENDER'] ?? self::RENDER_RETURN);
	}

	public static function onEndBufferContent(int $injectionId, array $settings, string &$content) : void
	{
		if ((string)static::$widgetContent === '' || mb_strpos($content, 'YandexPay') !== false) { return; }

		$content .= static::$widgetContent;
	}

	protected static function testRequest() : bool
	{
		$request = static::getRequest();

		return (
			!$request->isAdminSection()
			&& $request->isAjaxRequest()
		);
	}

	protected static function getUrlVariants() : array
	{
		$result = parent::getUrlVariants();

		$scriptName = static::getRequest()->getScriptName();

		if ($scriptName !== null)
		{
			$result = array_merge($result, [urldecode($scriptName)]);
		}

		return $result;
	}

	protected static function getComponentParameters(Injection\Setup\Model $setup, array $data = []) : array
	{
		$params = [
			'FACTORY_OPTIONS' => [
				'preserve' => false
			]
		];
		return $params + parent::getComponentParameters($setup, $data);
	}
}