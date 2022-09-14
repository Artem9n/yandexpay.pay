<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Settings\Reference\FieldsetCollection;

class ScheduleOptions extends FieldsetCollection
{
	use Concerns\HasMessage;

	public function getItemReference() : string
	{
		return ScheduleOption::class;
	}

	public function getTimeZoneOffset() : float
	{
		$date = new Main\Type\DateTime();

		return (int)$date->format('Z') / 60;
	}

	public function getMeaningfulValues() : array
	{
		$result = [];
		$intervals = [];

		foreach ($this->getOptions() as $option)
		{
			$fromWeekday = $option->getFromWeekday();
			$toWeekday = $option->getToWeekday();
			$timeInterval = [
				'start' => $option->getStart(),
				'end' => $option->getEnd(),
			];

			if ($fromWeekday > $toWeekday) { continue; }

			for ($day = $fromWeekday; $day <= $toWeekday; $day++)
			{
				$intervals[$day] = $timeInterval;
			}
		}

		ksort($result);

		foreach ($intervals as $day => $time)
		{
			$codeDay = self::getMessage(sprintf('CODE_DAY_%s', $day));
			$result[$codeDay] = $time;
		}

		return $result;
	}

	/** @return ScheduleOption[] */
	public function getOptions() : array
	{
		$result = [];

		foreach ($this->collection as $option)
		{
			$result[] = $option;
		}

		return $result;
	}

}
