<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery;

use Bitrix\Sale;

abstract class AbstractAdapter
{
	abstract public function getServiceType() : string;

	abstract public function isMatch(Sale\Delivery\Services\Base $service) : bool;

	abstract public function markSelected(Sale\OrderBase $order, array $store = []) : void;

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
}