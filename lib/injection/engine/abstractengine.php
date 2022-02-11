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

		if (static::$handlerDisallowYaPay) { return; }

		static::$handlerDisallowYaPay = true;

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

		return $data + [
			'MODE' => $options->getMode(),
			'SELECTOR' => $options->getSelector(),
			'POSITION' => $options->getPosition(),
			'TRADING_ID' => $setup->getTradingId(),
			'VARIANT_BUTTON' => $options->getVariant(),
			'WIDTH_BUTTON' => $options->getWidth(),
		];
	}

	protected static function getUrl() : string
	{
		return Main\Context::getCurrent()->getRequest()->getRequestUri();
	}

	protected static function testUrl(string $path) : bool
	{
		$url = static::getUrl();

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