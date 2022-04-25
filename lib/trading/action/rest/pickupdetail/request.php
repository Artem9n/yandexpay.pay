<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupDetail;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Rest;

class Request extends Rest\Reference\EffectiveRequest
{
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getItems() : Rest\Dto\Cart\Items
	{
		return $this->getChildCollection('cart.items');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getId() : string
	{
		return $this->requireField('pickupPointId');
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

	public function getLocationId() : ?string
	{
		[$deliveryId, $storeId, $locationId] = explode(':', $this->getId());

		return $locationId;
	}

	public function getCoupons() : ?array
	{
		return $this->getField('cart.coupons');
	}

	protected function collectionMap() : array
	{
		return [
			'cart.items' => Rest\Dto\Cart\Items::class,
		];
	}
}