<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Data;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Settings;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Utils;

class HolidayOption extends Settings\Reference\Fieldset
{
	use Concerns\HasMessage;

	public const DATE_FORMAT = 'DD.MM';
	public const DATE_GLUE = ',';

	protected $dateFormatPhp;
	protected $dateValuesCache = [];
	protected $calendar;

	protected function applyValues() : void
	{
		$this->applyCalendarMigration();
		$this->resetCalendar();
	}

	protected function applyCalendarMigration() : void
	{
		$calendarValue = (string)$this->getValue('CALENDAR');

		if ($calendarValue !== '') { return; }

		if ((string)$this->getValue('HOLIDAYS') === '' && (string)$this->getValue('WORKDAYS') === '')
		{
			$this->values['CALENDAR'] = Data\Holiday\Registry::BLANK;
		}
		else
		{
			$this->values['CALENDAR'] = Data\Holiday\Registry::MANUAL;
		}
	}

	public function isEmpty() : bool
	{
		$holidays = $this->getHolidays();
		$workdays = $this->getWorkdays();

		return (empty($holidays) && empty($workdays));
	}

	public function isHoliday(Main\Type\Date $date) : bool
	{
		$format = $this->getDateFormatPhp();
		$search = $date->format($format);

		return in_array($search, $this->getHolidays(), true);
	}

	public function isWorkday(Main\Type\Date $date) : bool
	{
		$format = $this->getDateFormatPhp();
		$search = $date->format($format);

		return in_array($search, $this->getWorkdays(), true);
	}

	/** @return string[] */
	public function getHolidays() : array
	{
		return $this->getCalendar()->holidays();
	}

	/** @return string[] */
	public function getWorkdays() : array
	{
		return $this->getCalendar()->workdays();
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getIntervals() : IntervalOption
	{
		return $this->getFieldset('INTERVALS');
	}

	public function getCalendar() : Data\Holiday\CalendarInterface
	{
		if ($this->calendar === null)
		{
			$this->calendar = $this->makeCalendar();
		}

		return $this->calendar;
	}

	protected function makeCalendar() : Data\Holiday\CalendarInterface
	{
		$type = $this->getValue('CALENDAR', Data\Holiday\Registry::MANUAL);
		$result = Data\Holiday\Registry::instance($type);

		if ($result instanceof Data\Holiday\Manual)
		{
			$result->setup(
				$this->getDateValues('HOLIDAYS'),
				$this->getDateValues('WORKDAYS')
			);
		}

		return $result;
	}

	protected function resetCalendar() : void
	{
		$this->calendar = null;
	}

	protected function getDateValues(string $key) : array
	{
		if (!isset($this->dateValuesCache[$key]))
		{
			$this->dateValuesCache[$key] = $this->makeDateValues($key);
		}

		return $this->dateValuesCache[$key];
	}

	protected function makeDateValues(string $key) : array
	{
		$gluedValue = (string)$this->getValue($key);
		$values = explode(static::DATE_GLUE, $gluedValue);
		$result = [];

		foreach ($values as $value)
		{
			$value = trim($value);

			if ($value === '') { continue; }

			if (preg_match('/^\d\D/', $value)) // need 2 start digits
			{
				$value = '0' . $value;
			}

			$result[] = $value;
		}

		return $result;
	}

	public function getHolidayFields(Entity\Reference\Environment $environment, string $siteId, array $defaults = []) : array
	{
		$result = [];
		$defaults += [
			'GROUP' => self::getMessage('HOLIDAY_GROUP'),
		];

		foreach ($this->getFields($environment, $siteId) as $name => $field)
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
			$message = (string)self::getMessage('HOLIDAY_' . $name . $suffix, null, '');

			if ($message === '') { continue; }

			$result[$resultKey] = $message;
		}

		return $result;
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'CALENDAR' => [
				'TYPE' => 'enumeration',
				'NAME' => self::getMessage('CALENDAR'),
				'HELP' => self::getMessage('CALENDAR_HELP'),
				'VALUES' => $this->getCalendarEnum(),
				'SETTINGS' => [
					'ALLOW_NO_VALUE' => 'N',
				],
			],
			'HOLIDAYS' => [
				'TYPE' => 'date',
				'NAME' => self::getMessage('HOLIDAYS'),
				'HELP' => self::getMessage('HOLIDAYS_HELP'),
				'SETTINGS' => [
					'FORMAT' => static::DATE_FORMAT,
					'GLUE' => static::DATE_GLUE,
					'SIZE' => 20,
					'ROWS' => 2,
				],
				'DEPEND' => [
					'CALENDAR' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Data\Holiday\Registry::MANUAL,
					],
				],
			],
			'WORKDAYS' => [
				'TYPE' => 'date',
				'NAME' => self::getMessage('WORKDAYS'),
				'HELP' => self::getMessage('WORKDAYS_HELP'),
				'SETTINGS' => [
					'FORMAT' => static::DATE_FORMAT,
					'GLUE' => static::DATE_GLUE,
					'SIZE' => 20,
					'ROWS' => 2,
				],
				'DEPEND' => [
					'CALENDAR' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Data\Holiday\Registry::MANUAL,
					],
				],
			],
			'INTERVALS' => $this->getIntervals()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'NAME' => self::getMessage('INTERVALS'),
				'HELP' => self::getMessage('INTERVALS_HELP'),
			],
		];
	}

	protected function getCalendarEnum() : array
	{
		$result = [];

		foreach (Data\Holiday\Registry::types() as $type)
		{
			$calendar = Data\Holiday\Registry::instance($type);

			$result[] = [
				'ID' => $type,
				'VALUE' => $calendar->title(),
			];
		}

		return $result;
	}

	protected function getFieldsetMap() : array
	{
		return [
			'INTERVALS' => IntervalOption::class,
		];
	}

	protected function getDateFormatPhp()
	{
		if ($this->dateFormatPhp === null)
		{
			$this->dateFormatPhp = Main\Type\Date::convertFormatToPhp(static::DATE_FORMAT);
		}

		return $this->dateFormatPhp;
	}
}
