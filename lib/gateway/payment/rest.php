<?php

namespace YandexPay\Pay\Gateway\Payment;

use YandexPay\Pay\Gateway;
use YandexPay\Pay\Reference\Concerns;

class Rest extends Gateway\Base
{
	use Concerns\HasMessage;

	public function getId() : string
	{
		return Gateway\Manager::REST;
	}

	public function getName() : string
	{
		return 'Rest';
	}

	protected function getUrlList() : array
	{
		return [];
	}

	public function getPaymentIdFromRequest() : ?int
	{
		return null;
	}

	public function refund() : void
	{
		// TODO: Implement refund() method.
	}
}