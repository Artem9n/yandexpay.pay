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

	public function searchOrder(EntityReference\Platform $platform, string $externalId) : ?int
	{
		$result = null;

		$query = Sale\TradingPlatform\OrderTable::getList([
			'filter' => [
				'=TRADING_PLATFORM_ID' => $platform->getId(),
				'=XML_ID' => $externalId,
			],
			'select' => [
				'ORDER_ID'
			],
		]);

		if ($row = $query->fetch())
		{
			$result = (int)$row['ORDER_ID'];
		}

		return $result;
	}

	public function loadOrder(int $orderId) : EntityReference\Order
	{
		/** @var \Bitrix\Sale\Order $orderClassName */
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		$orderClassName = $registry->getOrderClassName();
		$order = $orderClassName::load($orderId);

		if ($order === null)
		{
			throw new Main\SystemException('order not found', 'ORDER_NOT_FOUND');
		}

		return $this->makeOrder($order);
	}

	public function createOrder($siteId, $userId, $currency) : EntityReference\Order
	{
		if ($userId === null) { $userId = \CSaleUser::GetAnonymousUserID(); }

		Sale\DiscountCouponsManager::init(Sale\DiscountCouponsManager::MODE_CLIENT, ['userId' => $userId]);
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