<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup;

use Bitrix\Sale;

abstract class AbstractAdapter
{
	protected $service;

	public function __construct(Sale\Delivery\Services\Base $service)
	{
		$this->service = $service;
	}

	/**
	 * @return array{ID: string|int, LOCATION_ID: int, ADDRESS: string, DESCRIPTION: ?string, PHONE: ?string, EMAIL: ?string }
	 */
	abstract public function getStores(Sale\OrderBase $order) : array;
}