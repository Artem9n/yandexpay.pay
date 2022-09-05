<?php
namespace YandexPay\Pay\Trading\Entity\Common\Express;

use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const NEAREST = 'nearest';

	/** @var array */
	protected static $strategy;

	protected static function initStrategy() : void
	{
		if (static::$strategy !== null) { return; }

		$event = new Main\Event('yandexpay.pay', 'onStoreExpressStrategyClassNamesBuildList');
		$event->send();
		$resultList = $event->getResults();

		if (!is_array($resultList) && empty($resultList)) { return; }

		$customClasses = [];

		foreach ($resultList as $eventResult)
		{
			/** @var  Main\EventResult $eventResult*/
			if ($eventResult->getType() !== Main\EventResult::SUCCESS) { continue; }

			$params = $eventResult->getParameters();

			if(!empty($params) && is_array($params))
			{
				$customClasses += $params;
			}
		}

		if (empty($customClasses)) { return; }

		foreach ($customClasses as $type => $className)
		{
			static::$strategy[$type] = $className;
		}
	}

	protected static function userStrategies() : ?array
	{
		if (static::$strategy === null)
		{
			static::initStrategy();
		}

		return static::$strategy;
	}

	public static function types() : array
	{
		$result = [
			static::NEAREST,
		];

		$customTypes = static::userStrategies() ?? [];

		return array_merge($result, array_keys($customTypes));
	}

	public static function make(string $type) : AbstractStrategy
	{
		$customStrategy = static::userStrategies();
		$className = __NAMESPACE__ . '\\' . ucfirst($type) . 'Strategy';

		if ($customStrategy !== null && isset($customStrategy[$type]))
		{
			$className = $customStrategy[$type];
		}

		Assert::isSubclassOf($className, AbstractStrategy::class);

		return new $className();
	}
}