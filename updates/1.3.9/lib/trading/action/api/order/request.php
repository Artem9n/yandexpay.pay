<?php
namespace YandexPay\Pay\Trading\Action\Api\Order;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action;

class Request extends Action\Api\Request
{
	protected $orderId;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s', $this->getOrderNumber());
	}

	public function getMethod() : string
	{
		return Main\Web\HttpClient::HTTP_GET;
	}
}