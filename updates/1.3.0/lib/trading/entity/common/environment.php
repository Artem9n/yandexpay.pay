<?php

namespace YandexPay\Pay\Trading\Entity\Common;

use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

abstract class Environment extends EntityReference\Environment
{
	protected function createProduct() : EntityReference\Product
	{
		return new Product($this);
	}

	protected function createSite() : EntityReference\Site
	{
		return new Site($this);
	}

	protected function createRoute() : EntityReference\Route
	{
		return new Route($this);
	}

	protected function createStore() : EntityReference\Store
	{
		return new Store($this);
	}
}