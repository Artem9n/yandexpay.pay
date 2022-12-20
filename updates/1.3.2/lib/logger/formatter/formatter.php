<?php
namespace YandexPay\Pay\Logger\Formatter;

interface Formatter
{
	public function message() : string;

	public function context() : array;
}