<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings;

class ScheduleOption extends Settings\Reference\Fieldset
{
	use Concerns\HasMessage;

	public const MATCH_DAY = 'day';

	public const WEEKDAY_FIRST = 1;
	public const WEEKDAY_LAST = 7;

	public function getFromWeekday() : string
	{
		return $this->requireValue('FROM_WEEKDAY');
	}

	public function getToWeekday() : string
	{
		return $this->requireValue('TO_WEEKDAY');
	}

	public function getStart() : string
	{
		return $this->requireValue('START');
	}

	public function getEnd() : string
	{
		return $this->requireValue('END');
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'FROM_WEEKDAY' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'VALUES' => $this->getWeekdayEnum(),
			],
			'TO_WEEKDAY' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'VALUES' => $this->getWeekdayEnum(),
			],
			'START' => [
				'TYPE' => 'time',
			],
			'END' => [
				'TYPE' => 'time',
			],
		];
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
