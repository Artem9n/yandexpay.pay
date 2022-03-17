<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;

class OrderFinalizer
{
	public function __invoke(State\OrderCalculation $state)
	{
		$state->order->finalize();
	}
}

