<?php

namespace YandexPay\Pay\Gateway;

use Bitrix\Sale;

interface IGateway
{
	public function getId(): string;

	public function getName(): string;

	public function getParams(): array;

	public function startPay() : array;
}