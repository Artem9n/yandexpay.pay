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
			'select' => [ 'ORDER_ID' ],
			'order' => [ 'ID' => 'DESC' ],
			'limit' => 1,
		]);

		if ($row = $query->fetch())
		{
			$result = (int)$row['ORDER_ID'];
		}

		return $result;
	}

	public function load(string $paymentNumber) : EntityReference\Order
	{
		/** @var Sale\Payment $paymentClassName */
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		$paymentClassName = $registry->getPaymentClassName();

		$query = $paymentClassName::getList([
			'filter' => [ '=PS_INVOICE_ID' => $paymentNumber ],
			'select' => [ 'ID', 'ORDER_ID' ],
			'limit' => 1,
		]);

		// todo test is our payment

		if ($row = $query->fetch())
		{
			$order = $this->loadOrder($row['ORDER_ID']);
			$order->setPaymentId($row['ID']);

			return $order;
		}

		if (!is_numeric($paymentNumber))
		{
			throw new Main\SystemException('order not found', 'ORDER_NOT_FOUND');
		}

		return $this->loadOrder((int)$paymentNumber); // fallback to old behavior without link
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

	protected function makeOrder(Sale\Order $order) : Order
	{
		return new Order($this->environment, $order);
	}
}