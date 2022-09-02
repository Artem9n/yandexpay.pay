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

		if(!empty($customClasses))
		{
			Main\Loader::registerAutoLoadClasses(null, $customClasses);

			foreach ($customClasses as $className => $path)
			{
				$pos = mb_strpos($className, 'Strategy');
				$type = mb_strtolower(mb_substr($className, 0, $pos));
				static::$strategy[$type] = $className;
			}
		}
	}

	public static function getStrategyList() : ?array
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

		$customTypes = static::getStrategyList() ?? [];

		return array_merge($result, array_keys($customTypes));
	}

	public static function make(string $type) : AbstractStrategy
	{
		$customStrategy = static::getStrategyList();
		$className = __NAMESPACE__ . '\\' . ucfirst($type) . 'Strategy';

		if ($customStrategy !== null && isset($customStrategy[$type]))
		{
			$className = $customStrategy[$type];
		}

		Assert::isSubclassOf($className, AbstractStrategy::class);

		return new $className();
	}
}