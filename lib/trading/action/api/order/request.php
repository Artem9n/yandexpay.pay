<?php
namespace YandexPay\Pay\Trading\Action\Api\Order;

use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action;

class Request extends Action\Api\Reference\Request
{
	protected $orderId;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s', $this->getOrderId());
	}

	protected function queryHeaders() : array
	{
		return [
			'X-Request-Id' => $this->getOrderId(),
			'X-Request-Timeout' => 20,
			'X-Request-Attempt' => 0
		];
	}

	public function getMethod() : string
	{
		return Main\Web\HttpClient::HTTP_GET;
	}

	public function setOrderId($orderId) : void
	{
		$this->orderId = $orderId;
	}

	public function getOrderId() : string
	{
		Assert::notNull($this->orderId, 'orderId','orderId not set');

		return (string)$this->orderId;
	}
}