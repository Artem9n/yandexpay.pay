<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use Sale\Handlers;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;

class TotalCollector extends ResponseCollector
{
	/** @var array */
	protected $items = [];

	public function __invoke(State\Order $state)
	{
		$this->write([
			'amount' => $this->sumPaid($state),
			'label' => null, // todo
		]);
	}

	protected function sumPaid(State\Order $state) : float
	{
		$result = 0;

		/** @var \Bitrix\Sale\Payment $payment */
		foreach ($state->order->getPaymentCollection() as $payment)
		{
			$handler = $state->environment->getPaySystem()->getHandler($payment->getPaymentSystemId());

			if (!($handler instanceof Handlers\PaySystem\YandexPayHandler)) { continue; }

			$result += $payment->getSum();
		}

		return $result;
	}
}

