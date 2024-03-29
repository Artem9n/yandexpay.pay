<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Event;
use YandexPay\Pay\Injection;

abstract class AbstractEngine extends Event\Base
{
	protected static $handlerDisallowYaPay = false;

	protected static function loadModule(string $name) : void
	{
		if (!Main\Loader::includeModule($name))
		{
			throw new Main\SystemException(sprintf('missing %s module', $name));
		}
	}

	protected static function testRequest() : bool
	{
		$request = static::getRequest();

		return (
			!$request->isAdminSection()
			//&& !$request->isAjaxRequest()
			&& mb_strpos($request->getRequestedPage(), '/bitrix/') !== 0
		);
	}

	protected static function getRequest()
	{
		return Main\Context::getCurrent()->getRequest();
	}

	protected static function render(int $injectionId, array $data = []) : void
	{
		global $APPLICATION;

		if (SITE_ID !== $data['SITE_ID']) { return; }

		$setup = Injection\Setup\Model::wakeUp(['ID' => $injectionId]);
		$setup->fill();

		$parameters = static::getComponentParameters($setup, $data);

		ob_start();
		$APPLICATION->IncludeComponent('yandexpay.pay:button', '', $parameters, false);
		$contents = ob_get_clean();

		Main\Page\Asset::getInstance()->addString($contents, false, Main\Page\AssetLocation::AFTER_JS);
	}

	protected static function getComponentParameters(Injection\Setup\Model $setup, array $data = []) : array
	{
		/** @var Injection\Behavior\AbstractBehavior $options */
		$options = $setup->wakeupOptions();

		Assert::typeOf($options, Injection\Behavior\BehaviorInterface::class, 'options');

		$display = $options->getDisplay();

		return $data + [
			'MODE' => $options->getMode(),
			'SELECTOR' => $options->getSelector(),
			'POSITION' => $options->getPosition(),
			'TRADING_ID' => $setup->getTradingId(),
			'DISPLAY_TYPE' => $display->getType(),
			'DISPLAY_PARAMETERS' => $display->getWidgetParameters(),
			'USE_DIVIDER' => $options->useDivider(),
			'JS_CONTENT' => $options->getJsContent(),
			'CSS_CONTENT' => $options->getCssContent(),
		];
	}

	protected static function getUrl() : ?string
	{
		$url = Main\Context::getCurrent()->getRequest()->getRequestUri();

		return $url !== null ? urldecode($url) : null;
	}

	protected static function testUrl(string $path) : bool
	{
		$url = static::getUrl();

		if ($url === null) { return false; }

		if ($url === $path) { return true; }

		$url = static::normalize($url);

		return $path === $url;
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
}