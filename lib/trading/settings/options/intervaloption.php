<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Settings;
use YandexPay\Pay\Trading\Entity;

class IntervalOption extends Settings\Reference\Fieldset
{
	use Concerns\HasMessage;

	public const MATCH_UNTIL_END = 'untilEnd';
	public const MATCH_AFTER_START = 'afterStart';
	public const MATCH_FULL = 'full';

	public function isValid() : bool
	{
		$from = $this->getFromTime();
		$to = $this->getToTime();

		return ($from !== null && $to !== null && $from < $to);
	}

	public function isMatch(Main\Type\Date $date, $rule = IntervalOption::MATCH_FULL) : bool
	{
		if (!($date instanceof Main\Type\DateTime)) { return true; }

		return $this->isMatchTime($date, $rule);
	}

	public function isMatchTime(Main\Type\DateTime $date, $rule = IntervalOption::MATCH_FULL) : bool
	{
		$dateTime = $date->format('H:i');

		return $this->isMatchTimeValue($dateTime, $rule);
	}

	public function isMatchTimeValue($time, $rule = IntervalOption::MATCH_FULL) : bool
	{
		if ($time === null) { return true; }

		$fromTime = $this->getFromTime();
		$toTime = $this->getToTime();
		$result = true;

		if ($fromTime !== null && $time < $fromTime && $rule !== static::MATCH_UNTIL_END)
		{
			$result = false;
		}
		else if ($toTime !== null && $time > $toTime && $rule !== static::MATCH_AFTER_START)
		{
			$result = false;
		}

		return $result;
	}

	public function getFromTime() : ?string
	{
		return $this->getValue('FROM_TIME') ?: null;
	}

	public function getToTime() : ?string
	{
		return $this->getValue('TO_TIME') ?: null;
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'FROM_TIME' => [
				'TYPE' => 'time',
				'NAME' => self::getMessage('FROM_TIME'),
			],
			'TO_TIME' => [
				'TYPE' => 'time',
				'NAME' => self::getMessage('TO_TIME'),
			],
		];
	}
}
