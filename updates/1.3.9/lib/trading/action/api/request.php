<?php
namespace YandexPay\Pay\Trading\Action\Api;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action as TradingAction;

class Request extends TradingAction\Api\Reference\Request
{
	protected $orderNumber;

	public function getPath() : string
	{
		return '';
	}

	protected function queryHeaders() : array
	{
		return [
			'X-Request-Id' => $this->getOrderNumber(),
			'X-Request-Timeout' => 20,
			'X-Request-Attempt' => 0,
		];
	}

	public function setOrderNumber(string $orderNumber) : void
	{
		$this->orderNumber = $orderNumber;
	}

	protected function getOrderNumber() : string
	{
		Assert::notNull($this->orderNumber, 'orderNumber','orderNumber not set');

		return (string)urlencode($this->orderNumber);
	}
}