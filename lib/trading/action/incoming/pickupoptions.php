<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

class PickupOptions extends Common
{
	public function getBounds() : PickupOptions\Bounds
	{
		return $this->getChildModel('bounds');
	}

	protected function modelMap() : array
	{
		return [
			'bounds' => PickupOptions\Bounds::class,
		];
	}
}