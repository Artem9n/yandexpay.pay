<?php
namespace YandexPay\Pay\Injection\Engine;

use YandexPay\Pay\Utils;

class ElementFast extends Element
{
	protected static $elementId;
	protected static $widgetContent;

	public static function OnProlog(int $injectionId, array $settings) : void
	{
		if (!static::testShow($settings)) { return; }

		static::$elementId = static::findProduct($settings);

		if (static::$elementId === null) { return; }

		Element::disable();

		[ $componentParameters, $solutionParameters ] = static::getRenderParameters($injectionId, [
			'SITE_ID' => $settings['SITE_ID'],
			'PRODUCT_ID' => static::$elementId,
			'FACTORY_OPTIONS' => [
				'preserve' => false
			]
		]);

		static::$widgetContent = static::render($componentParameters, $solutionParameters['RENDER'] ?? self::RENDER_RETURN);
	}

	public static function onEndBufferContent(int $injectionId, array $settings, string &$content) : void
	{
		if ((string)static::$widgetContent === '' || mb_strpos($content, 'YandexPay') !== false) { return; }

		$content .= static::$widgetContent;
	}

	protected static function testShow(array $settings) : bool
	{
		return (string)static::$widgetContent === ''
			&& (parent::testShow($settings) && static::testQuery($settings));
	}

	protected static function testQuery(array $settings = []) : bool
	{
		$checkParamsString = $settings['QUERY_CHECK_PARAMS'];

		if (empty($checkParamsString)) { return false; }

		$checkParams = explode('&', $checkParamsString);

		foreach ($checkParams as $param)
		{
			[$name, $value] = explode('=', $param);

			if ($value === '' || static::getUrlParamValue($name) !== $value)
			{
				return false;
			}
		}

		return true;
	}

	protected static function findProduct(array $settings) : ?int
	{
		$idParam = $settings['QUERY_ELEMENT_ID_PARAM'];

		if (empty($idParam))
		{
			return parent::findProduct($settings);
		}

		$parameter = self::getUrlParamValue($idParam);

		return is_numeric($parameter) ? (int)$parameter : null;
	}

	protected static function testRequest() : bool
	{
		$request = static::getRequest();

		return (
			!$request->isAdminSection()
			&& $request->isAjaxRequest()
		);
	}

	protected static function getUrlParamValue(string $param)
	{
		$queryValues = static::getRequest()->toArray();

		return Utils\BracketChain::get($queryValues, $param);
	}
}