<?php
namespace YandexPay\Pay\Trading\Action\Request;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Payment extends Model
{
	public const PAYMENT_CASH = 'CASH';
	public const PAYMENT_CARD = 'CARD';

	public function getPaymentType() : string
	{
		$result = $this->getField('type');

		Assert::notNull($result, 'type');
		Assert::isString($result, 'type');

		return $result;
	}

	public function isPaymentCash() : bool
	{
		$type = $this->getPaymentType();

		return $type === self::PAYMENT_CASH;
	}

	public function isPaymentCard() : bool
	{
		$type = $this->getPaymentType();

		return $type === self::PAYMENT_CARD;
	}
}