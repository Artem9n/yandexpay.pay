<?php
namespace YandexPay\Pay\Injection\Engine;

use YandexPay\Pay\Injection;
use YandexPay\Pay\Utils;

class ElementFast extends Element
{
	protected static $elementId;

	public static function OnProlog(int $injectionId, array $settings) : void
	{
		if (!static::testRequest() || !static::testQuery($settings)) { return; }

		static::$elementId = static::findProduct($settings);
		Element::disable();
	}

	public static function onEndBufferContent(int $injectionId, array $settings, string &$content) : void
	{
		if (static::$elementId === null) { return; }

		$content .= static::render($injectionId, ['PRODUCT_ID' => static::$elementId, 'SITE_ID' => $settings['SITE_ID']], self::RENDER_RETURN);
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

	protected static function getComponentParameters(Injection\Setup\Model $setup, array $data = []) : array
	{
		$params = [
			'FACTORY_OPTIONS' => [
				'preserve' => false
			]
		];
		return $params + parent::getComponentParameters($setup, $data);
	}

	protected static function getUrlParamValue(string $param)
	{
		$queryValues = static::getRequest()->getQueryList()->getValues();

		return Utils\BracketChain::get($queryValues, $param);
	}
}