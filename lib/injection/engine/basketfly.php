<?php
namespace YandexPay\Pay\Injection\Engine;

class BasketFly extends AbstractEngine
{
	protected static $widgetContent;

	public static function onProlog(int $injectionId, array $settings) : void
	{
		if (!static::testShow($settings)) { return; }

		if (!isset($settings['PATH']) || !static::testUrl($settings['PATH'])) { return; }

		[ $componentParameters, $solutionParameters ] = static::getRenderParameters(
			$injectionId, [
				'SITE_ID' => $settings['SITE_ID'],
				'FACTORY_OPTIONS' => [
					'preserve' => false
				]
			]
		);

		static::$widgetContent = static::render($componentParameters, $solutionParameters['RENDER'] ?? self::RENDER_RETURN);
	}

	public static function onEndBufferContent(int $injectionId, array $settings, string &$content) : void
	{
		if ((string)static::$widgetContent === '' || mb_strpos($content, 'YandexPay') !== false) { return; }

		$content .= static::$widgetContent;
	}

	protected static function testShow(array $settings) : bool
	{
		return (string)static::$widgetContent === '' && parent::testShow($settings);
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

		return array_unique($result);
	}
}