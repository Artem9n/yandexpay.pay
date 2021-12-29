<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

class Product extends Common
{
	public function getProductId() : int
	{
		return $this->requireField('productId');
	}

	public function getMode() : string
	{
		return $this->requireField('mode');
	}
}