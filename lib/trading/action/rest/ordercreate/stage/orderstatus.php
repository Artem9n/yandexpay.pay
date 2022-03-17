<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use Bitrix\Sale;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderStatus
{
	public function __invoke(State\OrderCalculation $state)
	{
		$this->fillStatus($state);
	}

	protected function fillStatus(State\OrderCalculation $state) : void
	{
		$statusResult = $state->order->setStatus(Sale\OrderStatus::getInitialStatus());

		Exceptions\Facade::handleResult($statusResult);
	}
}