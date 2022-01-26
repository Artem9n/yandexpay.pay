<?php

namespace Yandexpay\Pay\Gateway\Payment;

use YandexPay\Pay\Reference\Concerns;

class Rshb extends RbsSkeleton
{
	use Concerns\HasMessage;

	public function getId(): string
	{
		return 'rshb';
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