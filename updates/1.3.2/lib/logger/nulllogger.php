<?php
namespace YandexPay\Pay\Logger;

use YandexPay\Pay\Psr;

class NullLogger extends Psr\Log\AbstractLogger
{
	public function log($level, $message, array $context = []) : void
	{
		// nothing
	}
}