<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Dto;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Reference as ActionReference;

class Pickup extends ActionReference\Dto
{
	public function getId() : string
	{
		return (string)$this->requireField('pickupPointId');
	}

	public function getPickupId() : string
	{
		[$deliveryId, $storeId, $locationId] = explode(':', $this->getId());

		return $deliveryId;
	}

	public function getStoreId() : string
	{
		[$deliveryId, $storeId, $locationId] = explode(':', $this->getId());

		return $storeId;
	}

	public function getAddress() : string
	{
		return $this->requireField('address');
	}

	public function getLocationId() : ?string
	{
		[$deliveryId, $storeId, $locationId] = explode(':', $this->getId());

		return $locationId;
	}

	public function getAmount() : float
	{
		return $this->requireField('amount');
	}
}