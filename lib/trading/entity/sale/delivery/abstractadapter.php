<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery;

use Bitrix\Sale;

abstract class AbstractAdapter
{
	abstract public function getServiceType() : string;

	abstract public function isMatch(Sale\Delivery\Services\Base $service) : bool;

	public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void
	{

	}

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

	public function prepareCalculation(Sale\OrderBase $orderBase) : void
	{

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

	protected function zipProperty(Sale\OrderBase $order) : ?Sale\PropertyValue
	{
		$propertyCollection = $order->getPropertyCollection();
		$zipCode = $this->getZipCode($order);
		$itemProperty = null;

		if ($zipCode !== '')
		{
			/** @var \Bitrix\Sale\PropertyValue $property */
			foreach ($propertyCollection as $property)
			{
				if ($property->getField('CODE') !== $zipCode) { continue; }
				$itemProperty = $property;
				break;
			}
		}

		return $itemProperty ?? $propertyCollection->getDeliveryLocationZip();
	}

	protected function addressProperty(Sale\OrderBase $order) : ?Sale\PropertyValue
	{
		$propertyCollection = $order->getPropertyCollection();
		$addressCode = $this->getAddressCode($order);
		$itemProperty = null;

		if ($addressCode !== '')
		{
			/** @var \Bitrix\Sale\PropertyValue $property */
			foreach ($propertyCollection as $property)
			{
				if ($property->getField('CODE') !== $addressCode) { continue; }
				$itemProperty = $property;
				break;
			}
		}

		return $itemProperty ?? $propertyCollection->getAddress();
	}

	protected function getAddressCode(Sale\OrderBase $order) : string
	{
		return '';
	}

	protected function getZipCode(Sale\OrderBase $order) : string
	{
		return '';
	}
}