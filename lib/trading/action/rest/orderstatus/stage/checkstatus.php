<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderStatus\Stage;

use YandexPay\Pay\Trading\Action\Api;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class CheckStatus
{
	public function __invoke(State\OrderStatus $state)
	{
		$this->resolveCapture($state);
		$this->resolveCancel($state);
	}

	protected function resolveCapture(State\OrderStatus $state) : void
	{
		$holdStatus = $state->handler->orderStatusHold($state->payment);
		$captureStatus = $state->handler->orderStatusCapture($state->payment);

		$paymentStatus = $state->payment->getField('PS_STATUS_CODE');

		if (
			$paymentStatus !== EntitySale\Status::PAYMENT_STATUS_AUTHORIZE
			|| $state->payment->isPaid()
		)
		{
			return;
		}

		if ($state->status === $holdStatus && $state->handler->isAutoPay($state->payment))
		{
			$state->handler->confirm($state->payment);
		}
		else if ($state->status === $captureStatus)
		{
			$state->handler->confirm($state->payment);
		}
	}

	protected function resolveCancel(State\OrderStatus $state) : void
	{
		$cancelStatus = $state->handler->orderStatusCancel($state->payment);
		$paymentStatus = $state->payment->getField('PS_STATUS_CODE');

		if (
			$paymentStatus !== EntitySale\Status::PAYMENT_STATUS_AUTHORIZE
			|| $state->payment->isPaid()
		)
		{
			return;
		}

		if ($state->status === $cancelStatus)
		{
			$state->handler->cancel($state->payment);
		}
	}
}