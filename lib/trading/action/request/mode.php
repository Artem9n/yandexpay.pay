<?php
namespace YandexPay\Pay\Trading\Action\Request;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Mode extends Model
{
	public const MODE_PRODUCT = 'PRODUCT';
	public const MODE_CART = 'CART';

	public function getMode() : string
	{
		$result = $this->getField('mode');

		Assert::notNull($result, 'mode');
		Assert::isString($result, 'mode');

		return $result;
	}

	public function isProduct() : bool
	{
		$mode = $this->getMode();

		return $mode === self::MODE_PRODUCT;
	}

	public function isCart() : bool
	{
		$mode = $this->getMode();

		return $mode === self::MODE_CART;
	}
}