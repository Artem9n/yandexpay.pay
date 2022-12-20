<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Stage;

use YandexPay\Pay\Logger;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Rest\OrderWebhook;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderLogger
{
	use Concerns\HasMessage;

	protected $request;

	public function __construct(OrderWebhook\Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\Order $state)
	{
		$type = $this->request->getEvent();

		if ($type === 'ORDER_STATUS_UPDATED')
		{
			$order = $this->request->getOrder();
			$paymentStatus = $order->getPaymentStatus();
			$deliveryStatus = $order->getDeliveryStatus();

			if ($deliveryStatus !== null)
			{
				$this->logDelivery($state, $deliveryStatus);
			}

			if ($paymentStatus !== null)
			{
				$this->logPayment($state, $paymentStatus);
			}
		}
		else if ($type === 'OPERATION_STATUS_UPDATED')
		{
			$this->logOperation($state, $this->request->getOperation());
		}
	}

	protected function logPayment(State\Order $state, string $paymentStatus) : void
	{
		$message = self::getMessage(
			sprintf('PAYMENT_STATUS_%s', $paymentStatus), [
				'#ORDER_ID#' => $state->payment->getOrderId(),
			]
		);

		if ($paymentStatus === 'FAILED')
		{
			$state->logger->error($message, [
				'AUDIT' => Logger\Audit::OUTGOING_RESPONSE,
			]);
		}
		else
		{
			$state->logger->info($message, [
				'AUDIT' => Logger\Audit::OUTGOING_RESPONSE,
			]);
		}
	}

	protected function logDelivery(State\Order $state, string $deliveryStatus) : void
	{
		$message = self::getMessage(
			sprintf('DELIVERY_STATUS_%s', $deliveryStatus), [
				'#DELIVERY_ID#' => implode(', ', $state->order->getDeliveryIdList()),
			]
		);

		if ($deliveryStatus === 'FAILED')
		{
			$state->logger->error($message, [
				'AUDIT' => Logger\Audit::OUTGOING_RESPONSE,
			]);
		}
		else
		{
			$state->logger->info($message, [
				'AUDIT' => Logger\Audit::OUTGOING_RESPONSE,
			]);
		}
	}

	protected function logOperation(State\Order $state, OrderWebhook\Dto\Operation $operation) : void
	{
		$message = self::getMessage(
			sprintf('OPERATION_TYPE_%s', $operation->getType()), [
				'#ORDER_ID#' => $state->payment->getOrderId(),
			]
		);

		if ($operation->getStatus() === 'FAIL')
		{
			$state->logger->error(self::getMessage('OPERATION_FAIL', [
				'#MESSAGE#' => $message,
			]), ['AUDIT' => Logger\Audit::OUTGOING_RESPONSE]);
		}
		else
		{
			$state->logger->info(self::getMessage('OPERATION_INFO', [
				'#MESSAGE#' => $message,
			]), [ 'AUDIT' => Logger\Audit::OUTGOING_RESPONSE, ]);
		}
	}
}