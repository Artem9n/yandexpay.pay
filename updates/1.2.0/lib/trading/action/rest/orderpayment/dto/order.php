<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderPayment\Dto;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Reference as ActionReference;

class Order extends ActionReference\Dto
{
	public function getId() : string
	{
		return (string)$this->requireField('orderId');
	}

	public function getPaymentStatus() : string
	{
		return $this->requireField('paymentStatus');
	}

	public function getShippingStatus() : ?string
	{
		return $this->getField('shippingStatus');
	}
}