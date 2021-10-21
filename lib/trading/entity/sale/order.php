<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay\Reference\Assert;
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

	public function loadUserBasket() : Main\Result
	{
		try
		{
			$fuserId = \CSaleBasket::GetBasketUserID(true); // $this->internalOrder->getUserId();
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var Sale\BasketBase $basketClassName */
			$basketClassName = $registry->getBasketClassName();

			Assert::notNull($fuserId, 'fUserId');

			$basket = $basketClassName::loadItemsForFUser($fuserId, $this->internalOrder->getSiteId());

			if ($basket->count() === 0)
			{
				throw new Main\SystemException('todo message'); // todo
			}

			$result = $this->internalOrder->setBasket($basket);
		}
		catch (Main\SystemException $exception)
		{
			$result = new Main\Result();
			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}

		return $result;
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

		if ($basket === null)
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

	public function addProduct($productId, $count = 1, array $data = null) : Main\Result
	{
		$basket = $this->getBasket();
		$basketFields = $this->getProductBasketFields($productId, $count, $data);

		$result = $this->createBasketItem($basket, $basketFields);
		$addData = $result->getData();

		if (isset($addData['BASKET_ITEM']))
		{
			/** @var Sale\BasketItemBase $basketItem */
			$basketItem = $addData['BASKET_ITEM'];
			$basketCode = $basketItem->getBasketCode();
			$addData['BASKET_CODE'] = $basketCode;

			$result->setData($addData);
		}

		return $result;
	}

	protected function createBasketItem(Sale\BasketBase $basket, $basketFields)
	{
		/** @var Sale\BasketItemBase $basketItem */
		$result = new Main\Result();
		$basketItem = $basket->createItem($basketFields['MODULE'], $basketFields['PRODUCT_ID']);
		$settableFieldsMap = array_flip($basketItem::getSettableFields());
		$alreadySetFields = [
			'MODULE' => true,
			'PRODUCT_ID' => true,
		];

		// apply limits

		if (isset($basketFields['AVAILABLE_QUANTITY']))
		{
			$siblingsQuantity = $this->getBasketProductSiblingsFilledQuantity($basketItem);

			if ($siblingsQuantity > 0)
			{
				$basketFields['AVAILABLE_QUANTITY'] -= $siblingsQuantity;
			}

			if ($basketFields['AVAILABLE_QUANTITY'] < $basketFields['QUANTITY'])
			{
				$basketFields['QUANTITY'] = $basketFields['AVAILABLE_QUANTITY'];
			}
		}

		// properties

		$propertyCollection = $basketItem->getPropertyCollection();

		if (!empty($basketFields['PROPS']) && $propertyCollection)
		{
			$propertyCollection->redefine($basketFields['PROPS']);
		}

		$alreadySetFields += [
			'PROPS' => true,
		];

		// set presets

		$presetsFields = [
			'PRODUCT_PROVIDER_CLASS' => true,
			'CALLBACK_FUNC' => true,
			'PAY_CALLBACK_FUNC' => true,
			'SUBSCRIBE' => true,
		];
		$presets = array_intersect_key($basketFields, $presetsFields);
		$presets = array_intersect_key($presets, $settableFieldsMap);

		$basketItem->setFields($presets);
		$alreadySetFields += $presetsFields;

		// get provider data

		$providerResult = $this->getBasketItemProviderData($basket, $basketItem, $basketFields);

		if ($result->isSuccess())
		{
			$providerData = (array)$providerResult->getData();

			if (
				isset($providerData['AVAILABLE_QUANTITY'], $basketFields['AVAILABLE_QUANTITY'])
				&& $providerData['AVAILABLE_QUANTITY'] < $basketFields['AVAILABLE_QUANTITY']
			)
			{
				$basketFields['AVAILABLE_QUANTITY'] = $providerData['AVAILABLE_QUANTITY'];
			}

			$basketFields += $providerData;
		}
		else
		{
			$result->addErrors($providerResult->getErrors());
		}

		// set name

		if (isset($basketFields['NAME']))
		{
			$basketItem->setField('NAME', $basketFields['NAME']);
			$alreadySetFields += [
				'NAME' => true,
			];
		}

		// set quantity

		$setQuantityResult = $basketItem->setField('QUANTITY', $basketFields['QUANTITY']);

		if (!$setQuantityResult->isSuccess())
		{
			$this->fillBasketItemAvailableQuantity($basketItem, $basketFields);

			$result->addErrors($setQuantityResult->getErrors());
		}

		$alreadySetFields += [
			'QUANTITY' => true,
		];

		// set left fields

		$leftFields = array_diff_key($basketFields, $alreadySetFields);
		$leftFields = array_intersect_key($leftFields, $settableFieldsMap);

		$setLeftFields = $basketItem->setFields($leftFields);

		if (!$setLeftFields->isSuccess())
		{
			$result->addErrors($setLeftFields->getErrors());
		}

		$result->setData([
			'BASKET_ITEM' => $basketItem,
		]);

		return $result;
	}

	protected function fillBasketItemAvailableQuantity(Sale\BasketItemBase $basketItem, $fields)
	{
		$currentQuantity = (float)$basketItem->getQuantity();
		$requestedQuantity = (float)$fields['QUANTITY'];

		if ($currentQuantity < $requestedQuantity && isset($fields['AVAILABLE_QUANTITY']))
		{
			$siblingsQuantity = $this->getBasketProductSiblingsFilledQuantity($basketItem);
			$availableQuantity = (float)$fields['AVAILABLE_QUANTITY'] - $siblingsQuantity;

			if ($availableQuantity > $currentQuantity && $availableQuantity < $requestedQuantity)
			{
				$basketItem->setField('QUANTITY', $availableQuantity);
			}
		}
	}

	protected function getBasketProductSiblingsFilledQuantity(Sale\BasketItemBase $basketItem)
	{
		$result = 0;
		$searchProductId = (string)$basketItem->getProductId();

		if ($searchProductId !== '')
		{
			$basket = $basketItem->getCollection();

			/** @var Sale\BasketItemBase $basketItem*/
			foreach ($basket as $siblingItem)
			{
				if (
					$siblingItem !== $basketItem
					&& (string)$siblingItem->getProductId() === $searchProductId
					&& $siblingItem->canBuy()
				)
				{
					$result += $siblingItem->getQuantity();
				}
			}
		}

		return $result;
	}

	protected function getBasketItemProviderData(Sale\BasketBase $basket, Sale\BasketItemBase $basketItem, $basketFields)
	{
		$result = new Main\Result();
		$initialQuantity = $basketItem->getField('QUANTITY');

		$basketItem->setFieldNoDemand('QUANTITY', $basketFields['QUANTITY']); // required for get available quantity

		$providerData = Sale\Provider::getProductData($basket, ['PRICE', 'AVAILABLE_QUANTITY'], $basketItem);
		$basketCode = $basketItem->getBasketCode();

		if (empty($providerData[$basketCode]))
		{
			$errorMessage = 'TRADING_ENTITY_SALE_ORDER_PRODUCT_NO_PROVIDER_DATA';//static::getLang('TRADING_ENTITY_SALE_ORDER_PRODUCT_NO_PROVIDER_DATA');
			$result->addError(new Main\Error($errorMessage));
		}
		else
		{
			// -- cache provider data to discount

			$discount = $basket->getOrder()->getDiscount();

			if ($discount instanceof Sale\Discount)
			{
				$basketFieldsDiscountData = array_intersect_key($basketFields, [
					'BASE_PRICE' => true,
					'DISCOUNT_PRICE' => true,
					'PRICE' => true,
					'CURRENCY' => true,
					'DISCOUNT_LIST' => true,
				]);
				$discountBasketData = $basketFieldsDiscountData + $providerData[$basketCode];

				$discount->setBasketItemData($basketCode, $discountBasketData);
			}

			// -- export data

			$result->setData($providerData[$basketCode]);
		}

		$basketItem->setFieldNoDemand('QUANTITY', $initialQuantity); // reset initial quantity

		return $result;
	}

	protected function getProductBasketFields($productId, $count = 1, array $data = null)
	{
		$result = [
			'PRODUCT_ID' => $productId,
			'QUANTITY' => $count,
			'CURRENCY' => $this->internalOrder->getCurrency(),
			'MODULE' => 'catalog',
			'PRODUCT_PROVIDER_CLASS' => Catalog\Product\Basket::getDefaultProviderName()
		];

		if ($data !== null)
		{
			$result = $data + $result; // user data priority
		}

		return $result;
	}

	public function applyCoupon(string $coupon) : Main\Result
	{
		$result = new Main\Result();

		$order = $this->internalOrder;

		Sale\DiscountCouponsManager::init(Sale\DiscountCouponsManager::MODE_CLIENT, ['userId' => $order->getUserId()]);

		if(!Sale\DiscountCouponsManager::add($coupon))
		{
			$message = 'TRADING_ENTITY_SALE_ORDER_PRODUCT_NO_APPLY_COUPON';
			$result->addError(new Main\Error($message));
		}
		else
		{
			$order->doFinalAction(true);
		}

		return $result;
	}

	public function getOrderPrice() : float
	{
		return $this->internalOrder->getPrice();
	}

	public function getUserId() : ?int
	{
		$userId = (int)$this->internalOrder->getUserId();

		return $userId > 0 ? $userId : null;
	}

	public function setStatus($status, $payload = null) : Main\Result
	{
		$result = new Main\Result();

		$saleResult = $this->internalOrder->setField('STATUS_ID', $status);

		if (!$saleResult->isSuccess())
		{
			$result->addError($saleResult->getErrors());
		}

		return $result;
	}
}