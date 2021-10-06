<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Sale;
use Bitrix\Main;

abstract class Order
{
	/** @var Environment */
	protected $environment;
	/** @var Sale\OrderBase */
	protected $internalOrder;

	public function __construct(Environment $environment, $internalOrder)
	{
		$this->environment = $environment;
		$this->internalOrder = $internalOrder;
	}

	/**
	 * @param int|string $productId
	 * @param int        $count
	 * @param array|null $data
	 *
	 * @return Main\Result
	 */
	public function addProduct($productId, int $count = 1, array $data = null) : Main\Result
	{
		throw new Main\NotImplementedException('addProduct is missing');
	}

	/**
	 * @param string $coupon
	 *
	 * @return Main\Result
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public function applyCoupon(string $coupon) : Main\Result
	{
		throw new Main\NotImplementedException('applyCoupon is missing');
	}

	/**
	 * @return float
	 */
	public function getOrderPrice() : float
	{
		throw new Main\NotImplementedException('getOrderPrice is missing');
	}
}