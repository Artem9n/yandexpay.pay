<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Event;
use YandexPay\Pay\Injection;

abstract class AbstractEngine extends Event\Base
{
	protected static $handlerDisallowYaPay = false;

	protected static function render(int $injectionId, array $data = []) : void
	{
		global $APPLICATION;

		if (static::$handlerDisallowYaPay) { return; }

		static::$handlerDisallowYaPay = true;

		$setup = Injection\Setup\Model::wakeUp(['ID' => $injectionId]);
		$setup->fill();

		$parameters = static::getComponentParameters($setup, $data);

		$APPLICATION->IncludeComponent('yandexpay.pay:button', '', $parameters, false);
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