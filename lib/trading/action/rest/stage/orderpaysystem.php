<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;

class OrderPaySystem
{
	public function __invoke(State\OrderCalculation $state)
	{
		$this->fillPaySystem($state);
	}

	protected function fillPaySystem(State\OrderCalculation $state) : void
	{
		$paySystemId = $state->options->getPaymentCard();

		if ((int)$paySystemId > 0)
		{
			$state->order->setPayments($paySystemId);
		}
	}
}