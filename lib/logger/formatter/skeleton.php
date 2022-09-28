<?php
namespace YandexPay\Pay\Logger\Formatter;

abstract class Skeleton implements Formatter
{
	public function forLogger() : array
	{
		return [$this->message(), $this->context()];
	}

	public function context() : array
	{
		return [];
	}
}