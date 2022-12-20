<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Dto;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Reference as ActionReference;

class Order extends ActionReference\Dto
{
	public function getId() : string
	{
		return (string)$this->requireField('orderId');
	}

	public function getPaymentStatus() : ?string
	{
		return $this->getField('paymentStatus');
	}

	public function getDeliveryStatus() : ?string
	{
		return $this->getField('deliveryStatus');
	}
}