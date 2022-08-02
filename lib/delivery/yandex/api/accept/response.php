<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\Accept;

use YandexPay\Pay\Trading\Action\Api;

class Response extends Api\Reference\Response
{
	public function getDeliveryStatus() : string
	{
		return (string)$this->requireField('data.delivery.status');
	}
}