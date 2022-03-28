<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup;

use Bitrix\Sale;

abstract class AbstractAdapter
{
	abstract public function isMatch(Sale\Delivery\Services\Base $service) : bool;

	/**
	 * @param \Bitrix\Sale\OrderBase              $order
	 * @param \Bitrix\Sale\Delivery\Services\Base $service
	 * @param array|null                          $bounds
	 *
	 * @return array{ID: string|int, LOCATION_ID: int, ADDRESS: string, DESCRIPTION: ?string, PHONE: ?string, EMAIL: ?string }
	 */
	abstract public function getStores(Sale\OrderBase $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array;
}