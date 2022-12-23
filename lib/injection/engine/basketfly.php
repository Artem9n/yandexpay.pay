<?php
namespace YandexPay\Pay\Injection\Engine;

use YandexPay\Pay\Injection;
class BasketFly extends AbstractEngine
{
	public static function onEndBufferContent(int $injectionId, array $settings, string &$content) : void
	{
		if (!static::testRequest()) { return; }

		if (!isset($settings['PATH']) || !static::testUrl($settings['PATH'])) { return; }

		$content .= static::render($injectionId, ['SITE_ID' => $settings['SITE_ID']],self::RENDER_RETURN);
	}

	protected static function testRequest() : bool
	{
		$request = static::getRequest();

		return (
			!$request->isAdminSection()
			&& $request->isAjaxRequest()
		);
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