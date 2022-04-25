<?php
namespace YandexPay\Pay\Trading\Action\Api\Operation;

use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action;

class Request extends Action\Api\Reference\Request
{
	protected $operationId;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/operations/%s', $this->getOperationId());
	}

	protected function queryHeaders() : array
	{
		return [
			'X-Request-Id' => $this->getOperationId(),
			'X-Request-Timeout' => 20,
			'X-Request-Attempt' => 0
		];
	}

	public function setOperationId($operationId) : void
	{
		$this->operationId = $operationId;
	}

	public function getOperationId() : string
	{
		Assert::notNull($this->operationId, 'orderId','orderId not set');

		return (string)$this->operationId;
	}
}