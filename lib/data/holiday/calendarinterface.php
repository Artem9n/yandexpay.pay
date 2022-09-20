<?php

namespace YandexPay\Pay\Data\Holiday;

interface CalendarInterface
{
	public function title();

	public function holidays();

	public function workdays();
}