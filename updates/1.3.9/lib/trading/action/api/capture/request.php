<?php
namespace YandexPay\Pay\Trading\Action\Api\Capture;

use YandexPay\Pay\Trading\Action;

class Request extends Action\Api\Request
{
	/** @var \Bitrix\Sale\Payment */
	protected $payment;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/capture', $this->getOrderNumber());
	}
}