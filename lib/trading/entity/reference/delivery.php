<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;
use Bitrix\Main\NotImplementedException;

class Delivery
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @return bool
	 */
	public function isRequired(): bool
	{
		return false;
	}

	/**
	 * @param string|null $siteId
	 *
	 * @return array{ID: string, VALUE: string, TYPE: string|null}[]
	 */
	public function getEnum($siteId = null) : array
	{
		throw new NotImplementedException('getEnum is missing');
	}

	/**
	 * @return int|null
	 */
	public function getEmptyDeliveryId() : ?int
	{
		throw new NotImplementedException('getEmptyDeliveryId is missing');
	}

	/**
	 * @param Order $order
	 *
	 * @return int[]
	 */
	public function getRestricted(Order $order) : array
	{
		throw new NotImplementedException('getRestricted is missing');
	}

	/**
	 * @param int $deliveryId
	 * @param Order $order
	 *
	 * @return bool
	 */
	public function isCompatible($deliveryId, Order $order) : bool
	{
		throw new NotImplementedException('isCompatible is missing');
	}

	/**
	 * @param int $deliveryId
	 * @param Order $order
	 *
	 * @return Delivery\CalculationResult
	 */
	public function calculate(int $deliveryId, Order $order) : Delivery\CalculationResult
	{
		throw new NotImplementedException('calculate is missing');
	}

	/**
	 * @param int $deliveryId
	 * @param string[]|null $supportedTypes
	 *
	 * @return string|null
	 */
	public function suggestDeliveryType(int $deliveryId, array $supportedTypes = null) : ?string
	{
		return null;
	}

	/**
	 * @param Order $order
	 * @param int   $deliveryId
	 *
	 * @return array
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public function getPickupStores(int $deliveryId, Order $order) : array
	{
		throw new NotImplementedException('getPickupStores is missing');
	}


	/**
	 * @param string $siteId
	 *
	 * @return Main\Result
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public function testAdminPickupCoords(string $siteId) : Main\Result
	{
		throw new NotImplementedException('testAdminPickupCoords is missing');
	}
}