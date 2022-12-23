<?php
namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Edost;

class Config
{
	public function getConfig()
	{
		return \CDeliveryEDOST::GetEdostConfig('all');
	}
}