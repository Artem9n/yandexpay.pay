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

	public function loadUserBasket() : Main\Result
	{
		throw new Main\NotImplementedException('loadUserBasket is missing');
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

	/**
	 * @return int|null
	 */
	public function getUserId() : ?int
	{
		throw new Main\NotImplementedException('getUserId is missing');
	}

	/**
	 * @param string $status
	 * @param mixed $payload
	 *
	 * @return Main\Result
	 */
	public function setStatus(string $status, $payload = null) : Main\Result
	{
		throw new Main\NotImplementedException('setStatus is missing');
	}
}