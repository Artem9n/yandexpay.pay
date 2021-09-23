<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use Bitrix\Sale;

/**
 * @property Environment $environment
 */
class OrderRegistry extends EntityReference\OrderRegistry
{
	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function createOrder($siteId, $userId, $currency) : EntityReference\Order
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		$orderClassName = $registry->getOrderClassName();
		$internalOrder = $orderClassName::create($siteId, $userId, $currency);

		return $this->makeOrder($internalOrder);
	}

	protected function makeOrder(Sale\OrderBase $order) : Order
	{
		return new Order($this->environment, $order);
	}
}