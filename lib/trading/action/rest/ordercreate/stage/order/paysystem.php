<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage\Order;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Action\Rest\State;

class Paysystem
{
	protected $paymentType;

	public function __construct(string $paymentType)
	{
		$this->paymentType = $paymentType;
	}

	public function __invoke(State\Order $state)
	{
		if ($this->paymentType !== 'SPLIT') { return; }

		$paySystemId = $state->options->getPaymentSplit() ?? $state->options->getPaymentCard();
		$paySystem = Sale\PaySystem\Manager::getById($paySystemId);

		/** @var Sale\Payment $entityPayment */
		foreach ($state->order->getPaymentCollection() as $entityPayment)
		{
			if ($entityPayment->isInner() || $paySystemId === $entityPayment->getId()) { continue; }
			$entityPayment->setField('PAY_SYSTEM_NAME', $paySystem['NAME']);
			$entityPayment->setField('PAY_SYSTEM_ID', $paySystemId);
		}

		$state->order->save();
	}
}