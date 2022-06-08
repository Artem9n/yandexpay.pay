<?php
namespace YandexPay\Pay\Trading\Action\Api\Cancel;

use Bitrix\Main;
use Bitrix\Sale\Payment;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Utils\Encoding;

class Request extends Action\Api\Reference\Request
{
	/** @var \Bitrix\Sale\Payment */
	protected $payment;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/cancel', $this->getPayment()->getOrderId());
	}

	protected function queryHeaders() : array
	{
		return [
			'X-Request-Id' => $this->getPayment()->getOrderId(),
			'X-Request-Timeout' => 20,
			'X-Request-Attempt' => 0
		];
	}

	public function getQuery() : array
	{
		$result = [
			'reason' => sprintf('cancel order %s', $this->getPayment()->getOrderId()),
		];

		return $result;
	}

	public function setPayment(Payment $payment) : void
	{
		$this->payment = $payment;
	}

	public function getPayment() : Payment
	{
		Assert::notNull($this->payment, 'payment','payment not set', Action\Reference\Exceptions\DtoProperty::class);

		return $this->payment;
	}
}