<?php
namespace YandexPay\Pay\Trading\Action\Request;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Coupon extends Model
{
	public function getCoupon() : string
	{
		$result = $this->getField('coupon');

		Assert::notNull($result, 'coupon');
		Assert::isString($result, 'coupon');

		return $result;
	}
}