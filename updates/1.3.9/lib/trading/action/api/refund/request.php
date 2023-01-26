<?php
namespace YandexPay\Pay\Trading\Action\Api\Refund;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action;

class Request extends Action\Api\Request
{
	protected $refundAmount;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/refund', $this->getOrderNumber());
	}

	public function getQuery() : array
	{
		$result = [
			'refundAmount' => $this->getRefundAmount(),
			'orderAmount' => 0,
		];

		return $result;
	}

	public function setRefundAmount(float $refundAmount)
	{
		$this->refundAmount = $refundAmount;
	}

	protected function getRefundAmount() : float
	{
		Assert::notNull($this->refundAmount, 'refundAmount','refundAmount not set');

		return (float)$this->refundAmount;
	}
}