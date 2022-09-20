<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;

/** @method ScheduleOption current() */
/** @property ScheduleOption[] collection */
class ScheduleOptions extends IntervalOptions
{
	use Concerns\HasMessage;

	public function getItemReference() : string
	{
		return ScheduleOption::class;
	}

	public function getWeeklyOptions() : array
	{
		$result = [];

		foreach ($this->collection as $option)
		{
			if ($option->isValid())
			{
				$result[] = $option;
			}
		}

		return $result;
	}

	public function validate() : Main\Result
	{
		$result = new Main\Result();

		$periods = $this->getWeeklyOptions();

		if(!empty($periods)) { return $result; }

		$result->addError(new Main\Error(self::getMessage('NOT_VALID_SCHEDULE')));

		return $result;
	}

}
