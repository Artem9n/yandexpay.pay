<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$this->write((string)$state->order->getId());
	}
}

