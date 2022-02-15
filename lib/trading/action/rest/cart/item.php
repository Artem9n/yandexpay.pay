<?php
namespace YandexPay\Pay\Trading\Action\Rest\Cart;

use YandexPay\Pay\Trading\Action\Reference as ActionReference;

class Item extends ActionReference\Skeleton
{
	public function getId() : string
	{
		return (string)$this->requireField('id');
	}

	public function getCount() : ?float
	{
		return $this->getField('quantity.count');
	}
}