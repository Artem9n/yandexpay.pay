<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

use YandexPay\Pay\Reference\Common\Model;

class DeliveryOptions extends Common
{
	public function getAddress() : Address
	{
		return $this->getChildModel('address');
	}

	protected function modelMap() : array
	{
		return [
			'address' => Address::class,
		];
	}
}