<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;

class OrderTotalCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$this->write([
			'amount' => $state->order->getOrderPrice(),
			'label' => null, // todo
		]);
	}
}

