<?php
namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Common as EntityCommon;

class Environment extends EntityCommon\Environment
{
	protected function getRequiredModules() : array
	{
		return [
			'catalog',
			'sale',
		];
	}

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

	protected function createPersonType() : EntityReference\PersonType
	{
		return new PersonType($this);
	}

	protected function createProperty() : EntityReference\Property
	{
		return new Property($this);
	}

	protected function createUserRegistry() : EntityReference\UserRegistry
	{
		return new UserRegistry($this);
	}

	protected function createCatalog() : EntityReference\Catalog
	{
		return new Catalog($this);
	}
}