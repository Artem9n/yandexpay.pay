<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Ozon;

use Bitrix\Sale\Shipment;
use Ipol;
use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Base extends AbstractAdapter
{
	protected $title;
	protected $typeVariant;

	public function getServiceType() : string
	{
		return EntitySale\Delivery::PICKUP_TYPE;
	}

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		return false;
	}

	public function load() : bool
	{
		return false;
	}

	protected function getType() : string
	{
		return '';
	}

	public function getStores(Sale\Order $order, Sale\Delivery\Services\Base $service, array $bounds = null) : array
	{
		if (!Main\Loader::includeModule('ipol.ozon')) { return []; }

		$stores = $this->loadStores($bounds);

		if (empty($stores)) { return []; }

		return $this->combineStores($stores);
	}

	protected function combineStores(array $stores) : array
	{
		$result = [];

		$locationsIds = $this->getLocationIds(array_keys($stores));

		foreach ($stores as $cityName => $pickupList)
		{
			if (!isset($locationsIds[$cityName])) { continue; }

			$result[$locationsIds[$cityName]] = $pickupList;
		}

		return $result;
	}

	protected function getLocationIds(array $locationsName) : array
	{
		$result = [];

		$query = Sale\Location\Name\LocationTable::getList([
			'filter' => [
				'NAME' => $locationsName,
				'=LANGUAGE_ID' => 'ru',
			],
			'select' => [
				'LOCATION_ID',
				'NAME',
			],
		]);

		while ($location = $query->fetch())
		{
			$result[$location['NAME']] = $location['LOCATION_ID'];
		}

		return $result;
	}

	protected function loadStores(array $bounds = null) : array
	{
		$result = [];

		if ($bounds === null) { return $result; }

		$type = Ipol\Ozon\Bitrix\Adapter::getVariantObjectTypesMap()[$this->typeVariant];

		$query =  Ipol\Ozon\VariantsTable::getList([
			'filter' => [
				'OBJECT_TYPE_ID' => $type,
				'<=LAT' => $bounds['ne']['latitude'],
				'>=LAT' => $bounds['sw']['latitude'],
				'<=LONG' => $bounds['ne']['longitude'],
				'>=LONG' => $bounds['sw']['longitude'],
			],
			'select' => [
				'ID', 'DELIVERY_VARIANT_ID', 'ADDRESS', 'NAME', 'LAT', 'LONG', 'WORKING_HOURS', 'PHONE', 'SETTLEMENT',
				'HOW_TO_GET',
			],
		]);

		while ($pickup = $query->fetch())
		{
			$locationName = $pickup['SETTLEMENT'];

			$result[$locationName][] = [
				'ID' => $pickup['DELIVERY_VARIANT_ID'],
				'ADDRESS' => $pickup['ADDRESS'],
				'TITLE' => sprintf('(%s) %s', $this->title, $pickup['NAME']),
				'GPS_N' => $pickup['LAT'],
				'GPS_S' => $pickup['LONG'],
				'SCHEDULE' => $pickup['WORKING_HOURS'],
				'PHONE' => $pickup['PHONE'],
				'DESCRIPTION' => $pickup['HOW_TO_GET'],
				'PROVIDER' => $this->getType(),
			];
		}

		return $result;
	}

	public function markSelected(Sale\Order $order, string $storeId = null, string $address = null) : void
	{
		if (!Main\Loader::includeModule('ipol.ozon')) { return; }

		$variantGuide = null;

		$shipments = $order->getShipmentCollection();

		/** @var Shipment $shipment */
		foreach ($shipments as $shipment)
		{
			if ($shipment->isSystem()) { continue; }

			$delivery = $shipment->getDelivery();

			if ($delivery === null) { continue; }

			if (!($delivery instanceof Ipol\Ozon\Bitrix\Handler\DeliveryHandlerProfile)) { continue; }

			if (!$delivery->getCalculator()) { continue; }

			$variantGuide = $delivery->getCalculator()->getSelectedVariantGuid();

		}

		$serviceLink = Ipol\Ozon\PvzWidgetHandler::getSavingLink();
		$_REQUEST[$serviceLink] = $storeId;

		/** @var \Bitrix\Sale\PropertyValue $property */
		foreach ($order->getPropertyCollection() as $property)
		{
			if (
				$variantGuide !== null
				&& $property->getField('CODE') === 'IPOL_OZON_DELIVERY_VARIANT'
			)
			{
				$property->setValue($variantGuide);
			}

			if ($property->getField('CODE') === 'IPOL_OZON_PVZ')
			{
				$property->setValue($storeId);
			}
		}

		$propAddress = $order->getPropertyCollection()->getAddress();

		if ($propAddress === null) { return; }

		$address = sprintf('%s (%s)', $address, \Ipol\Ozon\Bitrix\Tools::getMessage('WIDJET_PVZTYPE_' . $this->typeVariant));

		$propAddress->setValue($address);
	}
}