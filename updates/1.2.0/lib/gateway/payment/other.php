<?php

namespace YandexPay\Pay\Gateway\Payment;

use YandexPay\Pay\Gateway;
use YandexPay\Pay\Reference\Concerns;

class Other extends Gateway\BaseRest
{
	use Concerns\HasMessage;

	public function getName() : string
	{
		return self::getMessage('NAME');
	}

	public function getId() : string
	{
		return Gateway\Manager::OTHER;
	}

	public function getPaymentIdFromRequest() : ?int
	{
		return null;
	}

	public function refundSelf() : void
	{

	}

	protected function getUrlList() : array
	{
		return [];
	}
}