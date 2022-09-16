<?php

namespace YandexPay\Pay\Data\Holiday;

use Bitrix\Main;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Assert;

class Registry
{
	public const NATIONAL = 'national';
	public const PRODUCTION = 'production';
	public const BLANK = 'blank';
	public const MANUAL = 'manual';

	/** @var array<string, CalendarInterface>*/
	protected static $userMap;

	public static function types() : array
	{
		return array_merge(
			static::userTypes(),
			static::systemTypes()
		);
	}

	protected static function systemTypes() : array
	{
		return [
			static::PRODUCTION,
			static::NATIONAL,
			static::BLANK,
			static::MANUAL,
		];
	}

	protected static function userTypes() : array
	{
		$map = static::loadUserMap();

		return array_keys($map);
	}

	public static function instance(string $type) : CalendarInterface
	{
		if (in_array($type, static::systemTypes(), true))
		{
			$className = static::systemName($type);
		}
		else
		{
			$userMap = static::userMap();

			if (!isset($userMap[$type]))
			{
				throw new Main\SystemException(sprintf('unknown %s holiday calendar', $type));
			}

			$className = $userMap[$type];
		}

		Assert::classExists($className);
		Assert::isSubclassOf($className, CalendarInterface::class);

		return new $className();
	}

	protected static function systemName($type)
	{
		return __NAMESPACE__ . '\\' . ucfirst($type);
	}

	protected static function userMap() : array
	{
		if (static::$userMap === null)
		{
			static::$userMap = static::loadUserMap();
		}

		return static::$userMap;
	}

	protected static function loadUserMap() : array
	{
		$moduleName = Config::getModuleName();
		$eventName = 'onHolidayCalendar';
		$result = [];

		$event = new Main\Event($moduleName, $eventName);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() !== Main\EventResult::SUCCESS) { continue; }

			$parameters = $eventResult->getParameters();

			Assert::isArray($parameters, 'eventResult->getParameters()');

			foreach ($parameters as $code => $calendar)
			{
				Assert::classExists($calendar);
				Assert::isSubclassOf($calendar, CalendarInterface::class);

				$result[$code] = $calendar;
			}
		}

		return $result;
	}
}