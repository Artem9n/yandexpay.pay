<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Stage;

use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderSave
{
	public function __invoke(State\Payment $state)
	{
		$saveResult = $state->order->save();

		if (!$saveResult->isSuccess())
		{
			throw new DtoProperty($saveResult->getErrorMessages(), 'OTHER');
		}
	}
}

