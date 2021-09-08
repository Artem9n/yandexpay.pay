<?php

namespace Yandexpay\Pay;

use Yandexpay\Pay\Psr;

class Logger extends Psr\Log\AbstractLogger
{
	public function log($level, $message, array $context = []): void
	{
		\CEventLog::Add([

		]);
	}
}