<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading\Action\Rest\State;

class DeliveryCalculate
{
	public function __invoke(State\OrderCalculation $state)
	{
		$result = $state->order->recalculateShipment();

		Exceptions\Facade::handleResult($result);
	}
}