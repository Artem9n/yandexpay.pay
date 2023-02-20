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

		[ $elementId, $products ] = static::findProduct($settings);

		if ($elementId === null) { return; }

		Element::disable();

		[ $componentParameters, $solutionParameters ] = static::getRenderParameters($injectionId, [
			'SITE_ID' => $settings['SITE_ID'],
			'PRODUCT_ID' => $elementId,
			'PRODUCTS' => $products,
			'FACTORY_OPTIONS' => [
				'preserve' => false
			]
		]);

		if (!static::testRender($componentParameters)) { return; }

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

		$result = false;

		$checkParams = explode('&', $checkParamsString);

		foreach ($checkParams as $param)
		{
			[$name, $value] = explode('=', $param);

			if ($value !== '' && static::getUrlParamValue($name) === $value)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	protected static function findProduct(array $settings) : array
	{
		$idParam = $settings['QUERY_ELEMENT_ID_PARAM'];

		if (empty($idParam))
		{
			return parent::findProduct($settings);
		}

		$parameter = self::getUrlParamValue($idParam);

		if (!is_numeric($parameter))
		{
			return [];
		}

		$elementId = (int)$parameter;

		$products = static::findProducts($settings['IBLOCK'], $elementId);

		if (!isset($products[$elementId])) // isSku
		{
			$offerId = static::selectOffer(static::offerIblock($settings['IBLOCK']), $products);

			$selectedId = $offerId ?? $elementId;
		}
		else
		{
			$selectedId = $elementId;
		}

		return [ $selectedId, $products ];
	}

	protected static function testRequest(array $settings = []) : bool
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