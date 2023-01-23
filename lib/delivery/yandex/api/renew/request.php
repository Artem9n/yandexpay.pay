<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\Renew;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Delivery\Yandex\Api;

class Request extends Api\Reference\Request
{
	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/delivery/renew', $this->getOrderNumber());
	}
}