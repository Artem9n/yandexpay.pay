<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

class PickupOptions extends Common
{
	public function getBounds() : PickupOptions\Bounds
	{
		return $this->getChildModel('bounds');
	}

	public function getAddress() : string
	{
		return $this->requireField('address');
	}

	public function getItems() : Items
	{
		return $this->getChildCollection('items');
	}

	protected function modelMap() : array
	{
		return [
			'bounds' => PickupOptions\Bounds::class,
		];
	}

	protected function collectionMap() : array
	{
		return [
			'items' => Items::class,
		];
	}
}