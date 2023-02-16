<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Site\AvailableStore;

use Bitrix\Sale;

interface InterfaceStrategy
{
	public function resolve(array $stores, Sale\Order $order) : array;
}