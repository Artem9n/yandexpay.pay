<?php

namespace YandexPay\Pay\Gateway\Payment;

use YandexPay\Pay\Gateway;
use YandexPay\Pay\Trading\Action\Api;

class Rest extends Gateway\BaseRest
{
	public function getId() : string
	{
		return Gateway\Manager::REST;
	}

	public function isRest() : bool
	{
		return true;
	}

	public function startPay() : array
	{
		return [];
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