<?php
namespace YandexPay\Pay\Trading\Action\Rest;

use YandexPay\Pay\Trading\Action\Reference;

class OrderCreate extends Reference\Skeleton
{
	public function getCurrencyCode()
	{
		return $this->requireField('currencyCode');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getItems() : Cart\Items
	{
		return $this->getChildCollection('order.items');
	}

	protected function collectionMap() : array
	{
		return [
			'order.items' => Cart\Items::class,
		];
	}
}