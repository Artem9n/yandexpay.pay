<?php

namespace YandexPay\Pay\Gateway\Payment;

use Bitrix\Main\SystemException;
use YandexPay\Pay\Gateway\BaseRest;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Api;

class Rest
{
	public const STATUS_REFUND_PENDING = 'PENDING';  // на рассмотрение
	public const STATUS_REFUND_SUCCESS = 'SUCCESS';  // успешный

	/** @var BaseRest */
	protected $gateway;

	use Concerns\HasMessage;

	public function __construct(BaseRest $gateway)
	{
		$this->gateway = $gateway;
	}

	public function refund() : void
	{
		$request = new Api\Refund\Request();

		$isTestMode = $this->gateway->isTestHandlerMode();

		$apiKey = $isTestMode ?
			$this->gateway->getParameter('YANDEX_PAY_MERCHANT_ID', true)
			: $this->gateway->getParameter('YANDEX_PAY_REST_API_KEY', true);

		if ($apiKey === null) { return; }

		$request->setApiKey($apiKey);
		$request->setTestMode($isTestMode);
		$request->setPayment($this->gateway->getPayment());

		$data = $request->send();
		$response = $request->buildResponse($data, Api\Refund\Response::class);

		$operation = $response->getOperation();

		if ($operation->getStatus() === static::STATUS_REFUND_PENDING)
		{
			$messageCode = sprintf('OPERATION_%s_%s', $operation->getOperationType(), $operation->getStatus());
			throw new SystemException(static::getMessage($messageCode));
		}
	}

	public function startPay() : array
	{
		return [];
	}
}