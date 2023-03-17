<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery;

use Bitrix\Sale;

abstract class AbstractAdapter
{
	protected $title;
	protected $codeService;

	abstract public function load() : bool;

	abstract public function providerType() : ?string;

	abstract protected function addressCode(Sale\Order $order) : string;

	abstract protected function zipCode(Sale\Order $order) : string;

	public function serviceType() : string
	{
		return '';
	}

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		return false;
	}

	public function markSelectedPickup(Sale\Order $order, string $storeId, string $address) : void
	{

	}

	public function markSelectedCourier(Sale\Order $order, string $address, string $zip) : void
	{

	}

	public function getStores(Sale\Order $order, Sale\Delivery\Services\Base $service, array $bounds) : array
	{
		return [];
	}

	public function getDetailPickup(string $storeId) : array
	{
		return [];
	}

	public function prepareCalculateCourier(Sale\Order $order) : void
	{

	}

	public function prepareCalculatePickup(
		Sale\Order $order,
		int $deliveryId,
		string $pickupId,
		string $locationId,
		string $zip = null
	) : void
	{

	}

	public function onAfterOrderSave(Sale\Order $order) : void
	{

	}

	protected function zipProperty(Sale\Order $order) : ?Sale\PropertyValueBase
	{
		$propertyCollection = $order->getPropertyCollection();
		$zipCode = $this->zipCode($order);
		$itemProperty = null;

		if ($zipCode !== '')
		{
			/** @var Sale\PropertyValueBase $property */
			foreach ($propertyCollection as $property)
			{
				if ($property->getField('CODE') !== $zipCode) { continue; }
				$itemProperty = $property;
				break;
			}
		}

		return $itemProperty ?? $propertyCollection->getDeliveryLocationZip();
	}

	protected function addressProperty(Sale\Order $order) : ?Sale\PropertyValueBase
	{
		$propertyCollection = $order->getPropertyCollection();
		$addressCode = $this->addressCode($order);
		$itemProperty = null;

		if ($addressCode !== '')
		{
			/** @var Sale\PropertyValueBase $property */
			foreach ($propertyCollection as $property)
			{
				if ($property->getField('CODE') !== $addressCode) { continue; }
				$itemProperty = $property;
				break;
			}
		}

		return $itemProperty ?? $propertyCollection->getAddress();
	}

	protected function fillAddress(Sale\Order $order, string $address) : void
	{
		$property = $this->addressProperty($order);

		if ($property !== null)
		{
			$property->setValue($address);
		}
	}

	protected function fillZip(Sale\Order $order, string $zip) : void
	{
		$property = $this->zipProperty($order);

		if ($property !== null)
		{
			$property->setValue($zip);
		}
	}

	protected function getLocationIdsByNames(array $locationsName) : array
	{
		$result = [];

		$query = Sale\Location\Name\LocationTable::getList([
			'filter' => [
				'NAME' => $locationsName,
				'=LANGUAGE_ID' => 'ru'
			],
			'select' => [
				'LOCATION_ID',
				'NAME'
			],
		]);

		while ($location = $query->fetch())
		{
			$result[$location['NAME']] = $location['LOCATION_ID'];
		}

		return $result;
	}

	protected function getLocationIdsByCodes(array $locationsCodes) : array
	{
		$result = [];

		$query = Sale\Location\LocationTable::getList(array(
			'select' => [ 'ID', 'CODE' ],
			'filter' => [ '=CODE' => $locationsCodes ],
		));

		while ($row = $query->fetch())
		{
			$result[$row['CODE']] = $row['ID'];
		}

		return $result;
	}
}