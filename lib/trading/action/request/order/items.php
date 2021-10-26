<?php
namespace YandexPay\Pay\Trading\Action\Request\Order;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Items extends Model
{
	public function __construct(array $fields = [])
	{
		parent::__construct($fields);

		foreach ($this->fields as &$field)
		{
			Assert::notNull($field, 'items product');
			Assert::isArray($field, 'items product');

			$field = Product::initialize($field);
		}
		unset($field);
	}

	public function getProducts() : array
	{
		$result = [];

		/** @var Product $field */
		foreach ($this->fields as $field)
		{
			$result[$field->getId()] = $field->getQuantity();
		}

		return $result;
	}
}