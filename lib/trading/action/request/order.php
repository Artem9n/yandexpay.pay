<?php
namespace YandexPay\Pay\Trading\Action\Request;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Order extends Model
{
	public function getItems() : Model
	{
		$result = $this->getField('items');

		Assert::notNull($result, 'items');
		Assert::isArray($result, 'items');

		return Order\Items::initialize($result);
	}

	public function getTotal() : Model
	{
		$result = $this->getField('total');

		Assert::notNull($result, 'total');
		Assert::isArray($result, 'total');

		return Order\Total::initialize($result);
	}
}