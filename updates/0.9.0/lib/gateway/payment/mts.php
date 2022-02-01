<?php

namespace Yandexpay\Pay\Gateway\Payment;

use YandexPay\Pay\Gateway\Manager;
use YandexPay\Pay\Reference\Concerns;

class Mts extends RbsSkeleton
{
	use Concerns\HasMessage;

	public function getId(): string
	{
		return Manager::RBS_MTS;
	}

	protected function getTestUrl() : string
	{
		return 'https://web.rbsuat.com/mtsbank';
	}

	protected function getActiveUrl() : string
	{
		return 'https://oplata.mtsbank.ru/payment';
	}
}