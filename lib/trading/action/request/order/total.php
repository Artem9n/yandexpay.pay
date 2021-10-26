<?php
namespace YandexPay\Pay\Trading\Action\Request\Order;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Total extends Model
{
	public function getAmount() : string
	{
		$result = $this->getField('amount');

		Assert::notNull($result, 'total amount');
		Assert::isString($result, 'total amount');

		return $result;
	}
}