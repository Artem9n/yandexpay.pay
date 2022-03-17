<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;

class OrderCurrencyCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$this->write($state->order->getCurrencyCode());
	}
}

