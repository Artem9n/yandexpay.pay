<?php

namespace Yandexpay\Pay\Gateway\Payment;

use YandexPay\Pay\Gateway\Manager;
use YandexPay\Pay\Reference\Concerns;

class Alfabank extends RbsSkeleton
{
	use Concerns\HasMessage;

    public function getId(): string
    {
	    return Manager::RBS_ALFA;
    }

	public function getGatewayId() : string
	{
		return Manager::RBS_ALFA;
	}

	protected function getTestUrl() : string
	{
		return 'https://web.rbsuat.com/ab';
	}

	protected function getActiveUrl() : string
	{
		return 'https://pay.alfabank.ru/payment';
	}
}