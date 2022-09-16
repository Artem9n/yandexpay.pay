<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class Store
{
	public const FIELD_BEHAVIOR_WAREHOUSE = 'warehouse';
	public const FIELD_BEHAVIOR_CONTACT = 'contact';
	public const FIELD_BEHAVIOR_SHIPMENT_SCHEDULE = 'shipmentschedule';

	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function getFields(string $behavior = null) : array
	{
		throw new Main\NotImplementedException('getFields is missing');
	}

	public function expressStrategyEnum() : array
	{
		throw new Main\NotImplementedException('expressStrategyEnum is missing');
	}
}