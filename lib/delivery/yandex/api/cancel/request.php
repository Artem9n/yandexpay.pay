<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\Cancel;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action;

class Request extends Action\Api\Reference\Request
{
	/** @var int */
	protected $orderId;
	/** @var string */
	protected $cancelState;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/delivery/cancel', $this->getOrderId());
	}

	protected function queryHeaders() : array
	{
		return [
			'X-Request-Id' => $this->getOrderId(),
			'X-Request-Timeout' => 20,
			'X-Request-Attempt' => 0
		];
	}

	public function getQuery() : array
	{
		return [
			'cancelState' => $this->getCancelState(),
		];
	}

	public function setCancelState(string $cancelString) : void
	{
		$this->cancelState = $cancelString;
	}

	public function setOrderId(int $orderId) : void
	{
		$this->orderId = $orderId;
	}

	protected function getOrderId() : int
	{
		Assert::notNull($this->orderId, 'orderId','orderId not set', Action\Reference\Exceptions\DtoProperty::class);

		return $this->orderId;
	}

	protected function getCancelState() : string
	{
		Assert::notNull($this->cancelState, 'cancelState','cancelState not set', Action\Reference\Exceptions\DtoProperty::class);

		return $this->cancelState;
	}
}