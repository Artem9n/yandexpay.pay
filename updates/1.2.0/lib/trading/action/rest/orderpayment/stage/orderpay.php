<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderPayment\Stage;

use Bitrix\Sale;
use Bitrix\Main\Type;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Action\Api;
use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Action\Rest\OrderPayment\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderPay
{
	use Concerns\HasMessage;

	/** @var \YandexPay\Pay\Trading\Action\Rest\OrderPayment\Request  */
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\Payment $state)
	{
		$eventType = $this->request->getEvent();

		switch ($eventType)
		{
			case EntitySale\Status::EVENT_ORDER:
				$this->processOrder($state);
				break;
			case EntitySale\Status::EVENT_OPERATION:
				$this->processOperation($state);
				break;
		}
	}

	protected function processOrder(State\Payment $state) : void
	{
		if ($state->order->isCanceled()) { return; }

		$result = new Sale\Result();

		$status = $this->request->getOrder()->getPaymentStatus();

		$datetime = new \DateTime($this->request->getEventTime());
		$datetime->setTimezone((new Type\DateTime())->getTimeZone());

		$orderStatusCapture = $state->handler->orderStatusCapture($state->payment);
		$orderStatusHold = $state->handler->orderStatusHold($state->payment);
		$orderStatusCancel = $state->handler->orderStatusCancel($state->payment);
		$orderStatusRefund = $state->handler->orderStatusRefund($state->payment);
		$orderStatusPartiallyRefund = $state->handler->orderStatusPartiallyRefund($state->payment);

		$data = [
			'PS_STATUS_CODE' => $status,
			'PS_STATUS_DESCRIPTION' => $this->request->getEvent(),
			'PS_RESPONSE_DATE' => new Type\DateTime($datetime->format('d.m.Y H:i:s'))
		];

		if ($status === EntitySale\Status::PAYMENT_STATUS_CAPTURE)
		{
			$data['PAID'] = 'Y';

			if (!empty($orderStatusCapture))
			{
				$state->order->setField('STATUS_ID', $orderStatusCapture);
			}
		}
		else if ($status === EntitySale\Status::PAYMENT_STATUS_AUTHORIZE)
		{
			$data['PAID'] = 'Y';

			if (!empty($orderStatusHold))
			{
				$state->order->setField('STATUS_ID', $orderStatusHold);
			}
		}
		else if ($status === EntitySale\Status::PAYMENT_STATUS_VOID)
		{
			$data['PAID'] = 'N';

			if (!empty($orderStatusCancel))
			{
				$state->order->setField('STATUS_ID', $orderStatusCancel);
			}
		}
		else if ($status === EntitySale\Status::PAYMENT_STATUS_FAIL)
		{
			$result->addWarnings([new \Bitrix\Main\Error(EntitySale\Status::PAYMENT_STATUS_FAIL, 'WEBHOOK')]); //todo change?
		}
		else if ($status === EntitySale\Status::PAYMENT_STATUS_REFUND)
		{
			$data['PAID'] = 'N';

			if (!empty($orderStatusRefund))
			{
				$state->order->setField('STATUS_ID', $orderStatusRefund);
			}
		}
		else if ($status === EntitySale\Status::PAYMENT_STATUS_PARTIAL_REFUND)
		{
			if (!empty($orderStatusPartiallyRefund))
			{
				$state->order->setField('STATUS_ID', $orderStatusPartiallyRefund);
			}
		}

		\Bitrix\Sale\EntityMarker::addMarker($state->order, $state->payment, $result);

		$resultPayment = $state->payment->setFields($data);

		if (!$resultPayment->isSuccess())
		{
			throw new DtoProperty($resultPayment->getErrorMessages(), 'OTHER');
		}
	}

	protected function processOperation(State\Payment $state) : void
	{
		$result = new Sale\Result();

		$operation = $this->request->getOperation();

		if ($operation->getStatus() === 'FAIL')
		{
			$message = static::getMessage('OPERATION_TYPE_' . $operation->getType());
			$result->addWarnings([new \Bitrix\Main\Error($message)]);
		}

		\Bitrix\Sale\EntityMarker::addMarker($state->order, $state->payment, $result);
	}
}

