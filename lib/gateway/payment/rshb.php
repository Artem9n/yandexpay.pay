<?php

namespace Yandexpay\Pay\Gateway\Payment;

use YandexPay\Pay\Gateway\Manager;
use YandexPay\Pay\Reference\Concerns;

class Rshb extends RbsSkeleton
{
	use Concerns\HasMessage;

	public function getId(): string
	{
		return Manager::RBS_RSHB;
	}

	protected function getTestUrl() : string
	{
		return 'https://web.rbsuat.com/rshb/payment';
	}

	protected function getActiveUrl() : string
	{
		return 'https://rshb.rbsgate.com/payment';
	}
}