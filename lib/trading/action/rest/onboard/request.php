<?php
namespace YandexPay\Pay\Trading\Action\Rest\OnBoard;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Rest;

class Request extends Rest\Reference\EffectiveRequest
{
	public function getMerchantAuthToken() : string
	{
		return $this->requireField('merchantAuthToken');
	}

	public function getApiKey() : string
	{
		return $this->requireField('apiKey');
	}
}