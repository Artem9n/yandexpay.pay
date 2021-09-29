<?php

namespace YandexPay\Pay\Trading\Settings;

use YandexPay\Pay\Reference\Concerns;

class Options extends Reference\Skeleton
{
	use Concerns\HasMessage;
	
	public function getDeliveryOptions() : Options\DeliveryCollection
	{
		return $this->getFieldsetCollection('DELIVERY_OPTIONS');
	}

	public function getTabs() : array
	{
		return [
			'COMMON' => [
				'name' => self::getMessage('TAB_COMMON'),
				'sort' => 1000,
			],
		];
	}

	public function getFields($environment, string $siteId) : array
	{
		return
			$this->getHandlerFields($environment, $siteId)
			+ $this->getDeliveryFields($environment, $siteId)
			+ $this->getPickupFields($environment, $siteId);
	}

	protected function getHandlerFields($environment, string $siteId) : array
	{
		return [
			// todo
		];
	}

	protected function getDeliveryFields($environment, string $siteId) : array
	{
		$deliveryOptions = $this->getDeliveryOptions();
		
		return [
			'DELIVERY_STRICT' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('DELIVERY_GROUP'),
				'NAME' => self::getMessage('DELIVERY_STRICT'),
				'SORT' => 1000,
			],
			'DELIVERY_OPTIONS' => $deliveryOptions->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'GROUP' => self::getMessage('DELIVERY_GROUP'),
				'NAME' => self::getMessage('DELIVERY_OPTIONS'),
				'NOTE' => self::getMessage('DELIVERY_OPTIONS_NOTE'),
				'SORT' => 1010,
			],
		];
	}

	protected function getPickupFields($environment, string $siteId) : array
	{
		return [
			/*'PICKUP_PAYSYSTEM' => [
				'TYPE' => 'enumeration',
				''
			], todo*/
		];
	}

	protected function getFieldsetCollectionMap() : array
	{
		return [
			'DELIVERY_OPTIONS' => Options\DeliveryCollection::class,
		];
	}
}