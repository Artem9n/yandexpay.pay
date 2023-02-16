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

		if (!static::testRender($componentParameters)) { return; }

		static::$widgetContent = static::render($componentParameters, $solutionParameters['RENDER'] ?? self::RENDER_RETURN);
	}

	public static function onEndBufferContent(int $injectionId, array $settings, string &$content) : void
	{
		if ((string)static::$widgetContent === '' || mb_strpos($content, 'YandexPay') !== false) { return; }

		if (!static::isHtml($content)) { return; }

		$content .= static::$widgetContent;
	}

	protected static function isHtml(string $content)
	{
		return preg_match('/^\s*</m', $content); // first symbol is tag opener
	}

	protected static function testShow(array $settings) : bool
	{
		return (string)static::$widgetContent === '' && parent::testShow($settings);
	}

	protected static function testRequest(array $settings = []) : bool
	{
		$request = static::getRequest();

		if (static::isPathRegexp($settings['PATH']))
		{
			return parent::testRequest($settings);
		}

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