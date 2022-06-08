<?php
namespace YandexPay\Pay\Trading\Action\Rest\Authorize;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Rest;

class Request extends Rest\Reference\EffectiveRequest
{
	public function getOrderId() : string
	{
		return $this->requireField('orderId');
	}

	public function getHash() : string
	{
		return $this->requireField('hash');
	}

	public function getSuccessUrl() : string
	{
		return $this->requireField('successUrl');
	}
}