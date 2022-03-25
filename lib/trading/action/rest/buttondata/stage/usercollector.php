<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData\Stage;

use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Action\Rest\State;

class UserCollector extends ResponseCollector
{
	public function __invoke(State\OrderCalculation $state)
	{
		$userId = [
			'userId' => (string)$state->order->getUserId(),
			'fUserId' => $state->fUserId
		];

		$this->write(serialize($userId));
	}
}

