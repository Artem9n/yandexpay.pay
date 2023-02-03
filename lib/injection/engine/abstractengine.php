<?php
namespace YandexPay\Pay\Injection\Engine;

use Bitrix\Main;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Event;
use YandexPay\Pay\Injection;

abstract class AbstractEngine extends Event\Base
{
	public const RENDER_ASSETS = 'assets';
	public const RENDER_RETURN = 'return';
	public const RENDER_OUTPUT = 'output';

	protected static function loadModule(string $name) : void
	{
		if (!Main\Loader::includeModule($name))
		{
			throw new Main\SystemException(sprintf('missing %s module', $name));
		}
	}

	protected static function testShow(array $settings) : bool
	{
		return (
			SITE_ID === $settings['SITE_ID']
			&& static::testRequest($settings)
			&& static::testRender($settings)
		);
	}

	protected static function testRequest(array $settings = []) : bool
	{
		$request = static::getRequest();

		return (
			!$request->isAdminSection()
			&& !$request->isAjaxRequest()
			&& mb_strpos($request->getRequestedPage(), '/bitrix/') !== 0
		);
	}

	protected static function getRenderParameters(int $injectionId, array $data = []) : array
	{
		$injection = Injection\Setup\Model::wakeUp(['ID' => $injectionId]);
		$injection->fill();

		$behavior = $injection->wakeupOptions();

		Assert::typeOf($behavior, Injection\Behavior\BehaviorInterface::class, 'behavior');

		$display = $behavior->getDisplay();

		$solutionName = $injection->getTrading()->wakeupOptions()->getSolution();
		$solutionParameters = [];

		if ($solutionName !== null)
		{
			$solution = Injection\Solution\Registry::getInstance($solutionName);
			$solutionParameters = $solution->eventSettings($behavior);
		}

		$componentParameters = $data + [
			'MODE' => $behavior->getMode(),
			'SELECTOR' => $behavior->getSelector(),
			'POSITION' => $behavior->getPosition(),
			'TRADING_ID' => $injection->getTradingId(),
			'DISPLAY_TYPE' => $display->getType(),
			'DISPLAY_PARAMETERS' => $display->getWidgetParameters(),
			'USE_DIVIDER' => $behavior->useDivider(),
			'TEXT_DIVIDER' => $behavior->textDivider(),
			'JS_CONTENT' => $behavior->getJsContent(),
			'CSS_CONTENT' => $behavior->getCssContent(),
		];

		return [ $componentParameters, $solutionParameters ];
	}

	protected static function getRequest()
	{
		return Main\Context::getCurrent()->getRequest();
	}

	protected static function render(array $componentParameters, $mode = self::RENDER_ASSETS) : string
	{
		global $APPLICATION;

		$contents = '';

		if ($mode === self::RENDER_ASSETS)
		{
			$contents = $APPLICATION->IncludeComponent('yandexpay.pay:button', '', $componentParameters, false);
			Main\Page\Asset::getInstance()->addString($contents, false, Main\Page\AssetLocation::AFTER_JS);
		}
		else if ($mode === self::RENDER_RETURN)
		{
			$contents = $APPLICATION->IncludeComponent('yandexpay.pay:button', '', $componentParameters, false);
		}
		else if ($mode === self::RENDER_OUTPUT)
		{
			echo $APPLICATION->IncludeComponent('yandexpay.pay:button', '', $componentParameters, false);
		}

		return $contents;
	}

	protected static function testRender(array $parameters) : bool
	{
		$event = new Main\Event(Config::getModuleName(), 'onRenderYandexPay', $parameters);
		$event->send();
		$data = $event->getResults();

		if ($data) {
			foreach ($data as $evenResult) {
				if ($evenResult->getType() === \Bitrix\Main\EventResult::ERROR) {
					return false;
				}
			}
		}

		return true;
	}

	protected static function getUrlVariants() : array
	{
		$url = Main\Context::getCurrent()->getRequest()->getRequestUri();

		if ($url === null) { return []; }

		return [ urldecode($url) ];
	}

	protected static function testUrl(string $path) : bool
	{
		$result = false;

		$parts = explode(PHP_EOL, $path);

		if (empty($parts)) { return false; }

		foreach (static::getUrlVariants() as $url)
		{
			if ($url === null) { continue; }

			if ($result) { break; }

			$url = trim($url);
			$url = static::normalize($url);

			foreach ($parts as $part)
			{
				$part = trim($part);

				$matched = static::isPathRegexp($part)
					? static::testPathRegexp($part, $url)
					: $part === $url;

				if ($matched)
				{
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

	protected static function isPathRegexp(string $path) : bool
	{
		return mb_strpos($path, '*') !== false;
	}

	protected static function testPathRegexp(string $path, string $url) : bool
	{
		$regexp = str_replace(['#', '*'] , ['\\#', '.*?'], $path);
		$regexp = '#^' . $regexp . '$#i';

		return (bool)preg_match($regexp, $url);
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