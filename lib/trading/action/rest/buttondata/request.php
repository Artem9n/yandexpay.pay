<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Rest;

class Request extends Rest\Reference\EffectiveRequest
{
	public function getMode() : string
	{
		return $this->requireField('mode');
	}

	public function getProductId() : ?int
	{
		return $this->getField('productId');
	}

	public function getCurrencyCode() : string
	{
		return $this->requireField('currencyCode');
	}
}