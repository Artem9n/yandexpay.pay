<?php

namespace YandexPay\Pay\Data\Holiday;

use YandexPay\Pay\Reference\Concerns;

class Manual implements CalendarInterface
{
	use Concerns\HasMessage;

	protected $holidays = [];
	protected $workdays = [];

	public function title() : string
	{
		return self::getMessage('TITLE');
	}

	public function setup(array $holidays, array $workdays = []) : void
	{
		$this->holidays = $holidays;
		$this->workdays = $workdays;
	}

	public function holidays() : array
	{
		return $this->holidays;
	}

	public function workdays() : array
	{
		return $this->workdays;
	}
}