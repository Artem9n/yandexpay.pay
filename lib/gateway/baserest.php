<?php

namespace YandexPay\Pay\Gateway;

use YandexPay\Pay\Logger;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Api;

abstract class BaseRest extends Base
{
	use Concerns\HasMessage;

	public function refund(): void
	{
		if ($this->isRest())
		{
			$this->refundRest();
			return;
		}

		$this->refundSelf();
	}

	public function startPay() : array
	{
		return $this->startPaySelf();
	}

	public function isRest() : bool
	{
		$result = (
			Manager::resolveGatewayRest($this->getId())
			&& !empty($this->getParameter('YANDEX_PAY_REST_API_KEY', true))
		);

		if (
			isset($this->payment)
			&& $this->payment->getField('PS_STATUS_DESCRIPTION') === $this->getId())
		{
			$result = false;
		}

		return $result;
	}

	protected function logger() : Logger\Logger
	{
		$logger = new Logger\Logger();
		$logger->setLevel($this->getParameter('YANDEX_PAY_LOG_LEVEL', true));

		return $logger;
	}

	public function refundRest() : void
	{
		$logger = $this->logger();

		$request = new Api\Refund\Request();

		$isTestMode = $this->isTestHandlerMode();

		$apiKey = $isTestMode ?
			$this->getParameter('YANDEX_PAY_MERCHANT_ID', true)
			: $this->getParameter('YANDEX_PAY_REST_API_KEY', true);

		$orderNumber = $this->getPayment()->getField('PS_INVOICE_ID') ?: $this->getPayment()->getField('ORDER_ID'); // fallback to ORDER_ID without link
		$refundSum = $this->getPayment()->getField('PS_SUM') ?? $this->getPayment()->getSum();

		if ($apiKey === null) { return; }

		$request->setLogger($logger);
		$request->setApiKey($apiKey);
		$request->setTestMode($isTestMode);
		$request->setOrderNumber($orderNumber);
		$request->setRefundAmount($refundSum);

		$data = $request->send();
		$response = $request->buildResponse($data, Api\Refund\Response::class);

		$operation = $response->getOperation();

		$message = static::getMessage(
			sprintf('OPERATION_%s_%s', $operation->getOperationType(), $operation->getStatus()), [
				'#ORDER_ID#' => $this->getOrderId(),
			]
		);

		$logger->info($message, [
			'AUDIT' => Logger\Audit::OUTGOING_REQUEST,
		]);
	}
}