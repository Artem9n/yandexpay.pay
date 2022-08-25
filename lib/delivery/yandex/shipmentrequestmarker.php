<?php /** @noinspection PhpUnused */

namespace YandexPay\Pay\Delivery\Yandex;

use YandexPay\Pay as YandexPay;
use Bitrix\Sale;
use Bitrix\Main;

class ShipmentRequestMarker extends YandexPay\Reference\Event\Base
{
	public static function install() : void
	{
		foreach (static::getHandlers() as $handler)
		{
			static::register($handler);
		}
	}

	public static function uninstall() : void
	{
		foreach (static::getHandlers() as $handler)
		{
			static::unregister($handler);
		}
	}

	public static function getHandlers() : array
	{
		return [
			[
				'module' => 'sale',
				'event' => 'OnBeforeSaleShipmentSetField',
			],
		];
	}

	public static function onBeforeSaleShipmentSetField(Main\Event $event) : ?Main\EventResult
	{
		if ($event->getParameter('NAME') !== 'MARKED' || $event->getParameter('VALUE') !== 'Y') { return null; }

		/** @var Sale\Shipment $shipment */
		$shipment = $event->getParameter('ENTITY');
		$order = $shipment->getOrder();

		if ($order === null || !static::isOurShipment($shipment) || static::isShipmentChanged($shipment)) { return null; }

		$markers = (array)Sale\EntityMarker::getMarker($order->getInternalId(), $shipment);
		$hasTroubleMarkers = false;
		$hasOtherMarkers = false;

		foreach ($markers as $marker)
		{
			if ($marker['CODE'] !== 'DELIVERY_REQUEST_NOT_UPDATED')
			{
				$hasOtherMarkers = true;
				continue;
			}

			if ($marker['SUCCESS'] === Sale\EntityMarker::ENTITY_SUCCESS_CODE_DONE)
			{
				continue;
			}

			$hasTroubleMarkers = true;

			Sale\EntityMarker::updateMarker(
				null,
				array_merge($marker, [
					'SUCCESS' => Sale\EntityMarker::ENTITY_SUCCESS_CODE_DONE,
				]),
				$order,
				$shipment
			);
		}

		if ($hasTroubleMarkers && !$hasOtherMarkers)
		{
			return new Main\EventResult(Main\EventResult::ERROR);
		}

		return new Main\EventResult(Main\EventResult::SUCCESS);
	}

	protected static function isOurShipment(Sale\Shipment $shipment) : bool
	{
		return $shipment->getDelivery() instanceof YandexPay\Delivery\Yandex\Handler;
	}

	protected static function isShipmentChanged(Sale\Shipment $shipment) : bool
	{
		$result = false;

		/** @var Sale\ShipmentItem $item */
		foreach ($shipment->getShipmentItemCollection() as $item)
		{
			$itemChanges = array_intersect_key($item->getFields()->getChangedValues(), [
				'QUANTITY' => true,
			]);

			if (!empty($itemChanges))
			{
				$result = true;
				break;
			}
		}

		if ($shipment->getShipmentItemCollection()->isAnyItemDeleted())
		{
			$result = true;
		}

		return $result;
	}
}