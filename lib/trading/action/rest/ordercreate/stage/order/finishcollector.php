<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage\Order;

use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Action\Rest\State;

class FinishCollector extends ResponseCollector
{
	public function __invoke(State\Order $state)
	{
		$this->write((string)$state->order->getId());
	}
}