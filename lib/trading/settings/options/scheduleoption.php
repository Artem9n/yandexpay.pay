<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;

class ScheduleOption extends IntervalOption
{
	use Concerns\HasMessage;

	public const MATCH_DAY = 'day';

	public const WEEKDAY_FIRST = 1;
	public const WEEKDAY_LAST = 7;

	public function isMatchWeekday(int $weekday) : bool
	{
		$from = $this->getFromWeekday();
		$to = $this->getToWeekday();

		if ($from <= $to)
		{
			$result = ($weekday >= $from && $weekday <= $to);
		}
		else
		{
			$result = ($weekday >= $from || $weekday <= $to);
		}

		return $result;
	}

	public function getFromWeekday() : ?string
	{
		return $this->getValue('FROM_WEEKDAY') ?: null;
	}

	public function getToWeekday() : ?string
	{
		return $this->getValue('TO_WEEKDAY') ?: null;
	}

	protected function isValidWeekday($number) : bool
	{
		return ($number !== null && $number >= static::WEEKDAY_FIRST && $number <= static::WEEKDAY_LAST);
	}

	public function isValid() : bool
	{
		$timeValid = parent::isValid();
		$fromWeekday = $this->getFromWeekday();
		$toWeekday = $this->getToWeekday();

		return (
			$this->isValidWeekday($fromWeekday)
			&& $this->isValidWeekday($toWeekday)
			&& $timeValid
		);
	}

	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return parent::getFieldDescription($environment, $siteId) + [
				'SETTINGS' => [
					'SUMMARY' => '#FROM_WEEKDAY#-#TO_WEEKDAY# (#FROM_TIME#-#TO_TIME#)',
				],
			];
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$selfFields = [
			'FROM_WEEKDAY' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => static::getMessage('FROM_WEEKDAY'),
				'VALUES' => $this->getWeekdayEnum(),
			],
			'TO_WEEKDAY' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => static::getMessage('TO_WEEKDAY'),
				'VALUES' => $this->getWeekdayEnum(),
			],
		];

		return $selfFields + parent::getFields($environment, $siteId);
	}

	protected function getWeekdayEnum() : array
	{
		$result = [];

		for ($day = static::WEEKDAY_FIRST; $day <= static::WEEKDAY_LAST; ++$day)
		{
			$langKey = 'DOW_' . ($day % 7);

			$result[] = [
				'ID' => (string)$day,
				'VALUE' => self::getMessage($langKey),
			];
		}

		return $result;
	}
}
