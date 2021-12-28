<?php
namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class Environment
{
	protected $site;
	protected $orderRegisty;
	protected $userRegistry;
	protected $location;
	protected $delivery;
	protected $paySystem;
	protected $personType;
	protected $property;
	protected $catalog;
	protected $product;

	public function load() : void
	{
		$this->loadModules();
	}

	protected function loadModules() : void
	{
		foreach ($this->getRequiredModules() as $module)
		{
			if (!Main\Loader::includeModule($module))
			{
				throw new Main\SystemException('Cant load required module ' . $module);
			}
		}
	}

	protected function getRequiredModules() : array
	{
		return [];
	}

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
		throw new Main\NotImplementedException('createOrderRegistry is missing');
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
		throw new Main\NotImplementedException('getLocation is missing');
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
		throw new Main\NotImplementedException('getDelivery is missing');
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
		throw new Main\NotImplementedException('createPaySystem is missing');
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
		throw new Main\NotImplementedException('createSite is missing');
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
		throw new Main\NotImplementedException('PersonType is missing');
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
		throw new Main\NotImplementedException('Property is missing');
	}

	public function getUserRegistry() : UserRegistry
	{
		if ($this->userRegistry === null)
		{
			$this->userRegistry = $this->createUserRegistry();
		}

		return $this->userRegistry;
	}

	/**
	 * @return UserRegistry
	 */
	protected function createUserRegistry() : UserRegistry
	{
		throw new Main\NotImplementedException('UserRegistry is missing');
	}

	public function getCatalog() : Catalog
	{
		if ($this->catalog === null)
		{
			$this->catalog = $this->createCatalog();
		}

		return $this->catalog;
	}

	/**
	 * @return Catalog
	 */
	protected function createCatalog() : Catalog
	{
		throw new Main\NotImplementedException('catalog is missing');
	}

	public function getProduct() : Product
	{
		if ($this->product === null)
		{
			$this->product = $this->createProduct();
		}

		return $this->product;
	}

	protected function createProduct() : Product
	{
		throw new Main\NotImplementedException('catalog is missing');
	}
}