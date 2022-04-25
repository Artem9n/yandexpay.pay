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

		$request->setTestMode($this->gateway->isTestHandlerMode());
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