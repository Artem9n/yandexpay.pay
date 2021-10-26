<?php
namespace YandexPay\Pay\Trading\Action\Request\Order;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Product extends Model
{
	public function getLabel() : string
	{
		$result = $this->getField('label');

		Assert::notNull($result, 'product label');
		Assert::isString($result, 'product label');

		return $result;
	}

	public function getAmount() : string
	{
		$result = $this->getField('amount');

		Assert::notNull($result, 'product amount');
		Assert::isString($result, 'product amount');

		return $result;
	}

	public function getId() : int
	{
		$result = $this->getField('id');

		Assert::notNull($result, 'product id');
		Assert::isNumber($result, 'product id');

		return $result;
	}

	public function getQuantity() : int
	{
		$result = $this->getField('count');

		Assert::notNull($result, 'product count');
		Assert::isNumber($result, 'product count');

		return $result;
	}
}