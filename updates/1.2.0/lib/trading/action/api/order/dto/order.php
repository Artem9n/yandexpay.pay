<?php

namespace YandexPay\Pay\Trading\Action\Api\Order\Dto;

use YandexPay\Pay\Trading\Action;

class Order extends Action\Reference\Dto
{
	public function getId() : string
	{
		return $this->requireField('orderId');
	}
}