<?php
namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class Environment extends EntityReference\Environment
{
	protected function createOrderRegistry() : EntityReference\OrderRegistry
	{
		return new OrderRegistry($this);
	}

	protected function createLocation() : EntityReference\Location
	{
		return new Location($this);
	}

	protected function createDelivery() : EntityReference\Delivery
	{
		return new Delivery($this);
	}

	protected function createPaySystem() : EntityReference\PaySystem
	{
		return new PaySystem($this);
	}
}