<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Settings;
use YandexPay\Pay\Trading\Entity;

class ShipmentSchedule extends Settings\Reference\Fieldset
{
	use Concerns\HasMessage;

	public function getTimeZoneOffset() : int
	{
		$date = new Main\Type\DateTime();

		return (int)$date->format('Z') / 60;
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getSchedule() : ScheduleOptions
	{
		return $this->getFieldsetCollection('SCHEDULE');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getHoliday() : HolidayOption
	{
		return $this->getFieldset('HOLIDAY');
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return
			$this->getSelfFields($environment, $siteId)
			+ $this->getHolidayFields($environment, $siteId);
	}

	protected function getSelfFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'SCHEDULE' => $this->getSchedule()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'NAME' => self::getMessage('SCHEDULE'),
				'GROUP' => self::getMessage('SCHEDULE_GROUP'),
				'HELP' => self::getMessage('SCHEDULE_HELP'),
			],
		];
	}

	protected function getHolidayFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$result = [];
		$defaults = [
			'GROUP' => self::getMessage('HOLIDAY_GROUP'),
		];

		foreach ($this->getHoliday()->getFields($environment, $siteId) as $name => $field)
		{
			$key = sprintf('HOLIDAY[%s]', $name);
			$overrides = $this->getHolidayFieldOverrides($name);

			if (isset($field['DEPEND']))
			{
				$newDepend = [];

				foreach ($field['DEPEND'] as $dependName => $rule)
				{
					$newName = sprintf('[HOLIDAY][%s]', $dependName);
					$newDepend[$newName] = $rule;
				}

				$field['DEPEND'] = $newDepend;
			}

			$result[$key] = $overrides + $field + $defaults;
		}

		return $result;
	}

	protected function getHolidayFieldOverrides(string $name) : array
	{
		$langKeys = [
			'NAME' => '',
			'HELP_MESSAGE' => 'HELP',
		];
		$result = [];

		foreach ($langKeys as $resultKey => $type)
		{
			$suffix = ($type !== '' ? '_' . $type : '');
			$message = (string)static::getMessage('HOLIDAY_' . $name . $suffix, null, '');

			if ($message === '') { continue; }

			$result[$resultKey] = $message;
		}

		return $result;
	}

	protected function getFieldsetCollectionMap() : array
	{
		return [
			'SCHEDULE' => ScheduleOptions::class,
		];
	}

	protected function getFieldsetMap() : array
	{
		return [
			'HOLIDAY' => HolidayOption::class,
		];
	}
}