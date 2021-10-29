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

	/**
	 * @param int $personType
	 *
	 * @return Main\Result
	 */
	public function setPersonType(int $personType) : Main\Result
	{
		throw new Main\NotImplementedException('setPersonType is missing');
	}

	/**
	 * @param array $values
	 *
	 * @return Main\Result
	 */
	public function fillProperties(array $values) : Main\Result
	{
		throw new Main\NotImplementedException('fillProperties is missing');
	}

	/**
	 * @param int        $deliveryId
	 * @param float|null $price
	 * @param array|null $data
	 *
	 * @return Main\Result
	 */
	public function createShipment(int $deliveryId, float $price = null, array $data = null) : Main\Result
	{
		throw new Main\NotImplementedException('createShipment is missing');
	}

	/**
	 * @param int        $paySystemId
	 * @param float|null $price
	 * @param array|null $data
	 *
	 * @return Main\Result
	 */
	public function createPayment(int $paySystemId, float $price = null, array $data = null) : Main\Result
	{
		throw new Main\NotImplementedException('createPayment is missing');
	}

	/**
	 * @param string $externalId
	 *
	 * @return Main\Result
	 */
	public function add(string $externalId) : Main\Result
	{
		throw new Main\NotImplementedException('add is missing');
	}

	/**
	 * @return string|int
	 */
	public function getId()
	{
		throw new Main\NotImplementedException('add is missing');
	}

	/**
	 * @return Main\Result
	 */
	public function getBasketItemsData() : Main\Result
	{
		throw new Main\NotImplementedException('getBasketItemsData is missing');
	}

	/**
	 * @return Main\Result
	 */
	public function getBasket() : Sale\BasketBase
	{
		throw new Main\NotImplementedException('getBasket is missing');
	}
}