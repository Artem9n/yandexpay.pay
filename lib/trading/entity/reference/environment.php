<?php
namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main\NotImplementedException;

abstract class Environment
{
	protected $orderRegisty;
	protected $location;
	protected $delivery;
	protected $paySystem;

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

	public function getLocation() : Location // todo create
	{
		throw new NotImplementedException('getLocation is missing');
	}

	public function getDelivery() : Delivery // todo create
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
}