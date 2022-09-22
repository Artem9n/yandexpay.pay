<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderPaySystem
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->fillPaySystem($state);
	}

	protected function fillPaySystem(State\OrderCalculation $state) : void
	{
		$paymentType = $this->request->getPaymentType();

		if ($paymentType === 'CARD' || $paymentType === 'SPLIT')
		{
			$paySystemId = $state->options->getPaymentCard();
		}
		else
		{
			$paySystemId = $state->options->getPaymentCash();
		}

		if ((int)$paySystemId > 0)
		{
			$state->order->createPayment($paySystemId);
		}
	}
}