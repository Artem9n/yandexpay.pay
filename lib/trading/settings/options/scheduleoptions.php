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
