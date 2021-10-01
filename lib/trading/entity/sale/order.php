<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Catalog;

class Order extends EntityReference\Order
{
	/** @var Sale\OrderBase */
	protected $calculatable;

	public function __construct(Environment $environment, Sale\OrderBase $internalOrder)
	{
		parent::__construct($environment, $internalOrder);
	}

	public function loadUserBasket() : void
	{
		$fuserId = \CSaleBasket::GetBasketUserID(true);
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		$basketClassName = $registry->getBasketClassName();

		Assert::notNull($fuserId, 'fUserId');

		$basket = $basketClassName::loadItemsForFUser($fuserId, $this->internalOrder->getSiteId());
		$this->internalOrder->setBasket($basket);
	}

	public function setLocation($locationId) : Main\Result
	{
		$locationCode = \CSaleLocation::getLocationCODEbyID($locationId);

		return $this->setLocationPropertyValue($locationCode);
	}

	protected function setLocationPropertyValue($locationCode = null) : Main\Result
	{
		$propertyCollection = $this->internalOrder->getPropertyCollection();
		$locationProperty = $propertyCollection->getDeliveryLocation();
		$result = new Main\Result();

		if ($locationProperty === null)
		{
			$errorMessage = 'not property type location';//static::getLang('TRADING_ENTITY_SALE_ORDER_HASNT_LOCATION_PROPERTY');
			$result->addError(new Main\Error($errorMessage));

			return $result;
		}

		$locationProperty->setValue($locationCode);

		return $result;
	}

	/**
	 * @return Sale\OrderBase
	 */
	public function getCalculatable() : Sale\OrderBase
	{
		if ($this->calculatable === null)
		{
			$this->calculatable = $this->createCalculatable();
		}

		return $this->calculatable;
	}

	protected function createCalculatable() : Sale\OrderBase
	{
		$order = method_exists($this->internalOrder, 'createClone')
			? $this->internalOrder->createClone()
			: $this->internalOrder;

		$order->isStartField();

		$shipment = $this->getNotSystemShipment($order) ?: $this->initOrderShipment($order);

		if ($shipment !== null)
		{
			$shipment->setField('CUSTOM_PRICE_DELIVERY', 'N');
		}

		return $order;
	}

	protected function getNotSystemShipment(Sale\OrderBase $order = null) : ?Sale\Shipment
	{
		if ($order === null) { $order = $this->internalOrder; }

		$shipmentCollection = $order->getShipmentCollection();
		$result = null;

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$result = $shipment;
				break;
			}
		}

		return $result;
	}

	protected function initOrderShipment(Sale\OrderBase $order = null)
	{
		if ($order === null) { $order = $this->internalOrder; }

		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem();
		$shipment->setField('CURRENCY', $order->getCurrency());

		$this->fillShipmentBasket($shipment);

		return $shipment;
	}

	protected function fillShipmentBasket(Sale\Shipment $shipment) : void
	{
		/** @var Sale\BasketItem $basketItem */
		/** @var Sale\ShipmentItem $shipmentItem */
		$basket = $this->getBasket();
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		foreach ($basket as $basketItem)
		{
			$shipmentItem = $shipmentItemCollection->createItem($basketItem);

			if ($shipmentItem)
			{
				$shipmentItem->setQuantity($basketItem->getQuantity());
			}
		}
	}

	protected function getBasket() : Sale\BasketBase
	{
		$order = $this->internalOrder;
		$basket = $order->getBasket();

		if ($basket === null || $basket === false)
		{
			$basket = $this->createBasket($order);
			$order->setBasket($basket);
		}

		return $basket;
	}

	protected function createBasket(Sale\OrderBase $order)
	{
		$siteId = $order->getSiteId();
		$userId = $order->getUserId();
		$fUserId = null;

		if ($userId > 0)
		{
			$fUserId = Sale\Fuser::getIdByUserId($userId);
		}

		$registry = Sale\Registry::getInstance(Sale\Registry::ENTITY_ORDER);
		$basketClassName = $registry->getBasketClassName();
		$basket = $basketClassName::create($siteId);
		$basket->setFUserId($fUserId);

		return $basket;
	}

	public function addProduct($productId) : void
	{
		/*$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		$basketClassName = $registry->getBasketClassName();
		$basket = $basketClassName::create($siteId);

		if ($userId !== null)
		{
			$fUserId = Sale\Fuser::getIdByUserId($userId);
			$basket->setFUserId($fUserId);
		}

		Catalog\Product\Basket::addProductToBasket($basket, [
			'PRODUCT_ID' => $this->getProductId(),
		]);

		$order->setBasket($basket);*/
	}
}