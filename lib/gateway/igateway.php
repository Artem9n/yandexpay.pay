<?php

namespace Yandexpay\Pay\GateWay;

use Bitrix\Main\Request;
use Bitrix\Sale\Payment;

interface IGateWay
{
	public function getId(): string;

	public function getName(): string;

	public function getParams(): array;

	public function startPay(Payment $payment, Request $request) : array;
}