<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\CancelInfo;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Delivery\Yandex\Api;

class Request extends Api\Reference\Request
{
	public function getMethod() : string
	{
		return Main\Web\HttpClient::HTTP_GET;
	}

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/delivery/cancel-info', $this->getOrderNumber());
	}
}