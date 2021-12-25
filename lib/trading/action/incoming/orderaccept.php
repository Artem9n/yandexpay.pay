<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

class OrderAccept extends Common
{
	public function getDeliveryType() : string
	{
		return $this->requireField('deliveryType');
	}

	public function getOrderAmount() : float
	{
		return $this->requireField('orderAmount');
	}

	public function getAddress() : Address
	{
		return $this->getChildModel('address');
	}

	public function getPickup() : OrderAccept\Pickup
	{
		return $this->getChildModel('pickup');
	}

	public function getDelivery() : OrderAccept\Delivery
	{
		return $this->getChildModel('delivery');
	}

	public function getPaySystemId() : int
	{
		return (int)$this->requireField('paySystemId');
	}

	public function getUser() : OrderAccept\User
	{
		return $this->getChildModel('contact');
	}

	public function getItems() : Items
	{
		return $this->getChildCollection('items');
	}

	protected function modelMap() : array
	{
		return [
			'address' => Address::class,
			'pickup' => OrderAccept\Pickup::class,
			'delivery' => OrderAccept\Delivery::class,
			'contact' => OrderAccept\User::class,
		];
	}

	protected function collectionMap() : array
	{
		return [
			'items' => Items::class,
		];
	}
}