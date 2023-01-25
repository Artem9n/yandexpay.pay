<?php
namespace YandexPay\Pay\Trading\Action\Api\Cancel;

use YandexPay\Pay\Trading\Action;

class Request extends Action\Api\Request
{
	/** @var \Bitrix\Sale\Payment */
	protected $payment;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/cancel', $this->getOrderNumber());
	}

	public function getQuery() : array
	{
		$result = [
			'reason' => sprintf('cancel order %s', $this->getOrderNumber()),
		];

		return $result;
	}
}