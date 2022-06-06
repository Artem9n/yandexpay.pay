<?php

namespace YandexPay\Pay\Gateway;

abstract class BaseRest extends Base
{
	protected $restProxy;

	public function __construct()
	{
		parent::__construct();
		$this->restProxy = new Payment\Rest($this);
	}

	public function refund(): void
	{
		if ($this->isRest())
		{
			$this->restProxy->refund();
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
}