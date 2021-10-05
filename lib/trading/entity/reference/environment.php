<?php
namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main\NotImplementedException;

abstract class Environment
{
	protected $site;
	protected $orderRegisty;
	protected $location;
	protected $delivery;
	protected $paySystem;
	protected $personType;
	protected $property;

	public function getOrderRegistry() : OrderRegistry
	{
		if ($this->orderRegisty === null)
		{
			$this->orderRegisty = $this->createOrderRegistry();
		}

		return $this->orderRegisty;
	}

	protected function createOrderRegistry() : OrderRegistry
	{
		throw new NotImplementedException('createOrderRegistry is missing');
	}

	public function getLocation() : Location
	{
		if ($this->location === null)
		{
			$this->location = $this->createLocation();
		}

		return $this->location;
	}

	protected function createLocation() : Location
	{
		throw new NotImplementedException('getLocation is missing');
	}

	public function getDelivery() : Delivery
	{
		if ($this->delivery === null)
		{
			$this->delivery = $this->createDelivery();
		}

		return $this->delivery;
	}

	protected function createDelivery() : Delivery
	{
		throw new NotImplementedException('getDelivery is missing');
	}

	public function getPaySystem() : PaySystem
	{
		if ($this->paySystem === null)
		{
			$this->paySystem = $this->createPaySystem();
		}

		return $this->paySystem;
	}

	protected function createPaySystem() : PaySystem
	{
		throw new NotImplementedException('createPaySystem is missing');
	}

	public function getSite() : Site
	{
		if ($this->site === null)
		{
			$this->site = $this->createSite();
		}

		return $this->site;
	}

	protected function createSite() : Site
	{
		throw new NotImplementedException('createSite is missing');
	}

	public function getPersonType() : PersonType
	{
		if ($this->personType === null)
		{
			$this->personType = $this->createPersonType();
		}

		return $this->personType;
	}

	/**
	 * @return PersonType
	 */
	protected function createPersonType() : PersonType
	{
		throw new NotImplementedException('PersonType is missing');
	}

	public function getProperty() : Property
	{
		if ($this->property === null)
		{
			$this->property = $this->createProperty();
		}

		return $this->property;
	}

	/**
	 * @return Property
	 */
	protected function createProperty() : Property
	{
		throw new NotImplementedException('Property is missing');
	}
}