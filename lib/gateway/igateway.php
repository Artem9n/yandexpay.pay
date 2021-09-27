<?php

namespace YandexPay\Pay\GateWay;

interface IGateWay
{
	public function getId(): string;

	public function getName(): string;

	public function getParams(): array;

	public function startPay() : array;
}