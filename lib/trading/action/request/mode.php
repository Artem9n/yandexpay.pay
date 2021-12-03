<?php
namespace YandexPay\Pay\Trading\Action\Request;

use YandexPay\Pay\Injection;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Mode extends Model
{
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

		return $mode === Injection\Behavior\Registry::ELEMENT;
	}

	public function isBasket() : bool
	{
		$mode = $this->getMode();

		return $mode === Injection\Behavior\Registry::BASKET;
	}

	public function isOrder() : bool
	{
		$mode = $this->getMode();

		return $mode === Injection\Behavior\Registry::ORDER;
	}
}