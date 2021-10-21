<?php
namespace YandexPay\Pay\Trading\Action\Request;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Delivery extends Model
{
	public function getId() : string
	{
		$result = $this->getField('id');

		Assert::notNull($result, 'id');
		Assert::isString($result, 'id');

		return $result;
	}

	public function getLabel() : string
	{
		$result = $this->getField('label');

		Assert::notNull($result, 'label');
		Assert::isString($result, 'label');

		return $result;
	}

	public function getAmount() : string
	{
		$result = $this->getField('amount');

		Assert::notNull($result, 'amount');
		Assert::isString($result, 'amount');

		return $result;
	}

	public function getProvider() : string
	{
		$result = $this->getField('provider');

		Assert::notNull($result, 'provider');
		Assert::isString($result, 'provider');

		return $result;
	}

	public function getCategory() : string
	{
		$result = $this->getField('category');

		Assert::notNull($result, 'category');
		Assert::isString($result, 'category');

		return $this->getField('category');
	}

	public function getDate() : int
	{
		$result = $this->getField('date');

		Assert::notNull($result, 'date');
		Assert::isNumber($result, 'date');

		return $this->getField('date');
	}
}