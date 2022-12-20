<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use YandexPay\Pay\Trading\Settings;

/** @method IntervalOption current() */
/** @property IntervalOption[] collection */
class IntervalOptions extends Settings\Reference\FieldsetCollection
{
	public function getItemReference() : string
	{
		return IntervalOption::class;
	}

	public function hasValid() : bool
	{
		$result = false;

		foreach ($this->collection as $option)
		{
			if ($option->isValid())
			{
				$result = true;
				break;
			}
		}

		return $result;
	}
}