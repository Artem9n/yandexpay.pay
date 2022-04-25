<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderPayment;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Rest;

class Request extends Rest\Reference\EffectiveRequest
{
	public function getOrder() : Rest\OrderPayment\Dto\Order
	{
		return $this->getChildModel('order');
	}

	public function getOperation() : Rest\OrderPayment\Dto\Operation
	{
		return $this->getChildModel('operation');
	}

	public function getMerchantId() : string
	{
		return $this->requireField('merchantId');
	}

	public function getEventTime() : string
	{
		return $this->getField('eventTime');
	}

	public function getEvent() : string
	{
		return $this->requireField('event');
	}

	protected function modelMap() : array
	{
		return [
			'order' => Rest\OrderPayment\Dto\Order::class,
			'operation' => Rest\OrderPayment\Dto\Operation::class,
		];
	}
}