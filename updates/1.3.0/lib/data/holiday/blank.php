<?php

namespace YandexPay\Pay\Data\Holiday;

use YandexPay\Pay\Reference\Concerns;

class Blank implements CalendarInterface
{
	use Concerns\HasMessage;

	public function title() : string
	{
		return self::getMessage('TITLE');
	}

	public function holidays() : array
	{
		return [];
	}

	public function workdays() : array
	{
		return [];
	}
}