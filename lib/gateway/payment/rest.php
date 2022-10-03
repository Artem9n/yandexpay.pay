<?php

namespace YandexPay\Pay\Gateway\Payment;

use YandexPay\Pay\Gateway\BaseRest;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Api;
use YandexPay\Pay\Logger;

class Rest
{
	/** @var BaseRest */
	protected $gateway;

	use Concerns\HasMessage;

	public function __construct(BaseRest $gateway)
	{
		$this->gateway = $gateway;
	}

	protected function logger() : Logger\Logger
	{
		$logger = new Logger\Logger();
		$logger->setLevel($this->gateway->getParameter('YANDEX_PAY_LOG_LEVEL', true));

		return $logger;
	}

	public function refund() : void
	{
		$logger = $this->logger();

		$request = new Api\Refund\Request();

		$isTestMode = $this->gateway->isTestHandlerMode();

		$apiKey = $isTestMode ?
			$this->gateway->getParameter('YANDEX_PAY_MERCHANT_ID', true)
			: $this->gateway->getParameter('YANDEX_PAY_REST_API_KEY', true);

		if ($apiKey === null) { return; }

		$request->setLogger($logger);
		$request->setApiKey($apiKey);
		$request->setTestMode($isTestMode);
		$request->setPayment($this->gateway->getPayment());

		$data = $request->send();
		$response = $request->buildResponse($data, Api\Refund\Response::class);

		$operation = $response->getOperation();

		$message = static::getMessage(
			sprintf('OPERATION_%s_%s', $operation->getOperationType(), $operation->getStatus()), [
				'#ORDER_ID#' => $this->gateway->getOrderId(),
			]
		);

		$logger->info($message, [
			'AUDIT' => Logger\Audit::OUTGOING_REQUEST,
		]);
	}

	public function startPay() : array
	{
		return [];
	}
}