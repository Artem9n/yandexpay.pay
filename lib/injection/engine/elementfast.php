<?php
namespace YandexPay\Pay\Injection\Engine;

use YandexPay\Pay\Injection;

class ElementFast extends Element
{
	public static function onEndBufferContent(int $injectionId, array $settings, string &$content) : void
	{
		if (!static::testRequest()) { return; }

		if (!static::testQuery($settings['QUERY_PARAM'])) { return; }

		$elementId = static::findProduct($settings);

		if ($elementId === null) { return; }

		$content .= static::render($injectionId, ['PRODUCT_ID' => $elementId], static::RENDER_ASSETS);
	}

	protected static function testQuery(string $param) : bool
	{
		$result = false;

		[$code, $value] = explode('=', $param);

		$isHave = static::getRequest()->getQuery($code);

		if ($isHave !== null && $isHave === $value)
		{
			$result = true;
		}

		return $result;
	}

	protected static function testRequest() : bool
	{
		$request = static::getRequest();

		return (
			!$request->isAdminSection()
			&& $request->isAjaxRequest()
			&& mb_strpos($request->getRequestedPage(), '/bitrix/') !== 0
		);
	}
}