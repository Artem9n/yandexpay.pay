<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\Renew;

use Bitrix\Sale\Payment;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action;

class Request extends Action\Api\Reference\Request
{
	/** @var int */
	protected $orderId;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/delivery/renew', $this->getOrderId());
	}

	protected function queryHeaders() : array
	{
		return [
			'X-Request-Id' => $this->getOrderId(),
			'X-Request-Timeout' => 20,
			'X-Request-Attempt' => 0
		];
	}

	public function setOrderId(int $orderId) : void
	{
		$this->orderId = $orderId;
	}

	public function getOrderId() : int
	{
		Assert::notNull($this->orderId, 'orderId','orderId not set', Action\Reference\Exceptions\DtoProperty::class);

		return $this->orderId;
	}
}