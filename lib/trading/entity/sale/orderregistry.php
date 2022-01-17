<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use Bitrix\Sale;
use Bitrix\Main;

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
		if ($userId === null) { $userId = \CSaleUser::GetAnonymousUserID(); }

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		$orderClassName = $registry->getOrderClassName();
		$internalOrder = $orderClassName::create($siteId, $userId, $currency);

		return $this->makeOrder($internalOrder);
	}

	protected function makeOrder(Sale\OrderBase $order) : Order
	{
		return new Order($this->environment, $order);
	}

	public static function useAccountNumber() : bool
	{
		return (string)Main\Config\Option::get('sale', 'account_number_template') !== '';
	}
}