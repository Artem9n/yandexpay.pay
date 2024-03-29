<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

class DeliveryOptions extends Common
{
	public function getAddress() : Address
	{
		return $this->getChildModel('address');
	}

	public function getItems() : Items
	{
		return $this->getChildCollection('items');
	}

	public function getPaySystemId() : int
	{
		return $this->requireField('paySystemId');
	}

	protected function modelMap() : array
	{
		return [
			'address' => Address::class,
		];
	}

	protected function collectionMap() : array
	{
		return [
			'items' => Items::class,
		];
	}
}