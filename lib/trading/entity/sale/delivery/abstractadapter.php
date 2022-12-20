<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery;

use Bitrix\Sale;

abstract class AbstractAdapter
{
	abstract public function getServiceType() : string;

	abstract public function isMatch(Sale\Delivery\Services\Base $service) : bool;

	abstract public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void;

	abstract public function load() : bool;

	/**
	 * @param \Bitrix\Sale\OrderBase              $order
	 * @param \Bitrix\Sale\Delivery\Services\Base $service
	 * @param array|null                          $bounds
	 *
	 * @return array{ID: string|int, LOCATION_ID: int, ADDRESS: string, DESCRIPTION: ?string, PHONE: ?string, EMAIL: ?string }
	 */
	public function getStores(Sale\OrderBase $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		return [];
	}

	public function getDetailPickup(string $storeId) : array
	{
		return [];
	}

	public function prepareCalculatePickup(int $deliveryId, string $storeId, string $locationId, string $zip = null) : void
	{

	}

	public function markSelectedDelivery(Sale\OrderBase $order, array $address) : void
	{

	}

	public function onAfterOrderSave(Sale\OrderBase $order) : void
	{

	}
}