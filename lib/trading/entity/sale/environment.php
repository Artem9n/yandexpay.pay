<?php
namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class Environment extends EntityReference\Environment
{
	public function getOrderRegistry() : EntityReference\OrderRegistry
	{
		return new OrderRegistry($this);
	}

	public function getLocation() : EntityReference\Location
	{
		return new Location($this);
	}

	public function getDelivery() : EntityReference\Delivery
	{
		return new Delivery($this);
	}
}