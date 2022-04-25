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

		$data = [
			'PS_STATUS_CODE' => $status,
			'PS_STATUS_DESCRIPTION' => $this->request->getEvent(),
			'PS_RESPONSE_DATE' => new Type\DateTime($datetime->format('d.m.Y H:i:s'))
		];

		if (!$state->payment->isPaid())
		{
			if ($status === EntitySale\Status::PAYMENT_STATUS_CAPTURE)
			{
				$data['PAID'] = 'Y';
				$state->order->setField('STATUS_ID', $state->handler->orderStatusCapture($state->payment));
			}
			else if ($status === EntitySale\Status::PAYMENT_STATUS_AUTHORIZE)
			{
				$state->order->setField('STATUS_ID', $state->handler->orderStatusHold($state->payment));
			}
			else if ($status === EntitySale\Status::PAYMENT_STATUS_VOID)
			{
				$state->order->setField('STATUS_ID', $state->handler->orderStatusCancel($state->payment));
			}
			else if ($status === EntitySale\Status::PAYMENT_STATUS_FAIL)
			{
				$result->addWarnings([new \Bitrix\Main\Error(EntitySale\Status::PAYMENT_STATUS_FAIL, 'WEBHOOK')]); //todo change?
			}
		}
		else if ($status === EntitySale\Status::PAYMENT_STATUS_REFUND)
		{
			$data['PAID'] = 'N';
			$state->order->setField('STATUS_ID', $state->handler->orderStatusRefund($state->payment));
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

