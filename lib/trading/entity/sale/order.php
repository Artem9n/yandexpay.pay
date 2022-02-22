<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Catalog;

class Order extends EntityReference\Order
{
	use Concerns\HasMessage;

	/** @var Sale\OrderBase */
	protected $calculatable;
	protected $isStartField;

	public function __construct(Environment $environment, Sale\OrderBase $internalOrder)
	{
		parent::__construct($environment, $internalOrder);
	}

	public function initialize() : void
	{
		Sale\DiscountCouponsManager::init(Sale\DiscountCouponsManager::MODE_EXTERNAL);

		$this->freeze();
	}

	public function finalize() : Main\Result
	{
		$basket = $this->getBasket();

		$this->internalOrder->setMathActionOnly(false);
		$result = $basket->refreshData();

		if ($result->isSuccess())
		{
			$unfreezeResult = $this->unfreeze();

			if (!$unfreezeResult->isSuccess())
			{
				$result->addErrors($unfreezeResult->getErrors());
			}
		}

		return $result;
	}

	public function freeze() : void
	{
		$this->isStartField = $this->internalOrder->isStartField();
		$this->internalOrder->setMathActionOnly(true);
	}

	public function unfreeze()
	{
		$result = new Main\Result();

		$this->internalOrder->setMathActionOnly(false);

		if ($this->isStartField)
		{
			$hasMeaningfulFields = $this->internalOrder->hasMeaningfulField();
			$finalActionResult = $this->internalOrder->doFinalAction($hasMeaningfulFields);

			if (!$finalActionResult->isSuccess())
			{
				$result->addErrors($finalActionResult->getErrors());
			}
		}

		return $result;
	}

	public function initUserBasket() : Main\Result
	{
		try
		{
			$fuserId = $this->getFUserId();
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var Sale\BasketBase $basketClassName */
			$basketClassName = $registry->getBasketClassName();
			$basket = $basketClassName::loadItemsForFUser($fuserId, $this->internalOrder->getSiteId());

			$result = $this->internalOrder->setBasket($basket);

			if ($basket->count() === 0)
			{
				$result->addError(new Main\Error(self::getMessage('EMPTY_BASKET')));
			}
		}
		catch (Main\SystemException $exception)
		{
			$result = new Main\Result();
			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}

		return $result;
	}

	public function initEmptyBasket() : Main\Result
	{
		$siteId = $this->internalOrder->getSiteId();
		$fUserId = $this->getFUserId();

		$registry = Sale\Registry::getInstance(Sale\Registry::ENTITY_ORDER);
		$basketClassName = $registry->getBasketClassName();
		$basket = $basketClassName::create($siteId);
		$basket->setFUserId($fUserId);

		return $this->internalOrder->setBasket($basket);
	}

	public function getBasketItemData($basketCode) : Main\Result
	{
		$result = new Main\Result();
		$basket = $this->getBasket();
		$basketItem = $basket->getItemByBasketCode($basketCode);

		if ($basketItem === null)
		{
			$errorMessage = self::getMessage('BASKET_ITEM_NOT_FOUND');
			$result->addError(new Main\Error($errorMessage));

			return $result;
		}

		$result->setData([
			'BASKET_ID' => $basketItem->getBasketCode(),
			'PRODUCT_ID' => $basketItem->getProductId(),
			'NAME' => $basketItem->getField('NAME'),
			'PRICE' => $basketItem->getPriceWithVat(),
			'QUANTITY' => $basketItem->canBuy() ? $basketItem->getQuantity() : 0,
			'PROPS' => $this->collectBasketItemProps($basketItem),
		]);

		return $result;
	}

	public function setBasketItemPrice($basketCode, $price) : Main\Result
	{
		$result = new Main\Result();
		$basket = $this->getBasket();
		$basketItem = $basket->getItemByBasketCode($basketCode);

		if ($basketItem === null)
		{
			$errorMessage = self::getMessage('BASKET_ITEM_NOT_FOUND');
			$result->addError(new Main\Error($errorMessage));
		}
		else
		{
			$setResult = $basketItem->setFields([
				'CUSTOM_PRICE' => 'Y',
				'PRICE' => $price
			]);

			if (!$setResult->isSuccess())
			{
				$result->addErrors($setResult->getErrors());
			}
		}

		return $result;
	}

	protected function collectBasketItemProps(Sale\BasketItem $basketItem) : array
	{
		$result = [];

		/** @var \Bitrix\Sale\BasketPropertyItem $property */
		foreach ($basketItem->getPropertyCollection() as $property)
		{
			$id = $property->getId();
			$data = [
				'NAME' => $property->getField('NAME'),
				'CODE' => $property->getField('CODE'),
				'VALUE' => $property->getField('VALUE'),
			];

			if ($id !== null)
			{
				$data['ID'] = $id;
			}

			$result[] = $data;
		}

		return $result;
	}

	public function getOrderableItems() : array
	{
		$basket = $this->getBasket();
		$result = [];

		/** @var Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			if (!$basketItem->canBuy()) { continue; }
			if ($basketItem->getFinalPrice() <= 0) { continue; }

			$result[] = $basketItem->getBasketCode();
		}

		return $result;
	}

	public function setBasketItemQuantity($basketCode, $quantity) : Main\Result
	{
		$result = new Main\Result();
		$basket = $this->getBasket();
		$basketItem = $basket->getItemByBasketCode($basketCode);

		if ($basketItem === null)
		{
			$errorMessage = self::getMessage('BASKET_ITEM_NOT_FOUND');
			$result->addError(new Main\Error($errorMessage));
		}
		else if ((float)$quantity !== (float)$basketItem->getQuantity())
		{
			$setResult = $basketItem->setField('QUANTITY', $quantity);

			if (!$setResult->isSuccess())
			{
				$result->addErrors($setResult->getErrors());
			}
		}

		return $result;
	}

	public function deleteBasketItem(string $basketCode) : Main\Result
	{
		$result = new Main\Result();
		$basket = $this->getBasket();
		$basketItem = $basket->getItemByBasketCode($basketCode);

		if ($basketItem === null)
		{
			$errorMessage = self::getMessage('BASKET_ITEM_NOT_FOUND');
			$result->addError(new Main\Error($errorMessage));
		}
		else
		{
			$deleteResult = $basketItem->delete();

			if (!$deleteResult->isSuccess())
			{
				$result->addErrors($deleteResult->getErrors());
			}
		}

		return $result;
	}

	public function getBasketPrice()
	{
		return $this->getBasket()->getPrice();
	}

	public function setLocation($locationId) : Main\Result
	{
		$locationCode = \CSaleLocation::getLocationCODEbyID($locationId);

		return $this->setLocationPropertyValue($locationCode);
	}

	public function setLocationByCode($locationCode) : Main\Result
	{
		return $this->setLocationPropertyValue($locationCode);
	}

	protected function setLocationPropertyValue($locationCode = null) : Main\Result
	{
		$propertyCollection = $this->internalOrder->getPropertyCollection();
		$locationProperty = $propertyCollection->getDeliveryLocation();
		$result = new Main\Result();

		if ($locationProperty === null)
		{
			$errorMessage = self::getMessage('HASNT_LOCATION_PROPERTY');
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

	public function getBasket() : Sale\BasketBase
	{
		$order = $this->internalOrder;
		$basket = $order->getBasket();

		Assert::notNull($basket, '$order->getBasket()');

		return $basket;
	}

	protected function getFUserId() : int
	{
		$userId = (int)$this->internalOrder->getUserId();

		if ($userId <= 0 || $userId === (int)\CSaleUser::GetAnonymousUserID())
		{
			$result = Sale\Fuser::getId();
		}
		else
		{
			$result = Sale\Fuser::getId(true) ?: Sale\Fuser::getIdByUserId($userId) ?: null; // false to null
		}

		Assert::notNull($result, 'fUserId');

		return $result;
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

	public function getShipmentPrice(int $deliveryId) : ?float
	{
		$shipment = $this->getDeliveryShipment($deliveryId);

		return $shipment !== null ? $shipment->getPrice() : null;
	}

	public function setShipmentPrice(int $deliveryId, float $price) : Main\Result
	{
		$shipment = $this->getDeliveryShipment($deliveryId);

		if ($shipment === null)
		{
			$result = new Main\Result();
			$result->addError(new Main\Error(
				sprintf('cant find shipment with delivery service %s', $deliveryId)
			));
		}
		else
		{
			$result = $this->fillShipmentPrice($shipment, $price);
		}

		return $result;
	}

	protected function getDeliveryShipment(int $deliveryId) : ?Sale\Shipment
	{
		$deliveryId = (int)$deliveryId;
		$result = null;

		/** @var Sale\Shipment $shipment */
		foreach ($this->internalOrder->getShipmentCollection() as $shipment)
		{
			if ((int)$shipment->getDeliveryId() === $deliveryId)
			{
				$result = $shipment;
				break;
			}
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
			$providerData = $providerResult->getData();
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

	protected function getBasketItemProviderData(Sale\BasketBase $basket, Sale\BasketItemBase $basketItem, $basketFields)
	{
		$result = new Main\Result();

		$providerData = Sale\Provider::getProductData($basket, ['PRICE', 'AVAILABLE_QUANTITY'], $basketItem);
		$basketCode = $basketItem->getBasketCode();

		if (empty($providerData[$basketCode]))
		{
			$errorMessage = self::getMessage('PRODUCT_NO_PROVIDER_DATA');
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

		return $result;
	}

	protected function getProductBasketFields($productId, $count = 1, array $data = null)
	{
		$result = [
			'PRODUCT_ID' => $productId,
			'QUANTITY' => $count,
			'CURRENCY' => $this->internalOrder->getCurrency(),
			'MODULE' => 'catalog',
			'PRODUCT_PROVIDER_CLASS' => Catalog\Product\Basket::getDefaultProviderName(),
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
			$message = self::getMessage('PRODUCT_NO_APPLY_COUPON');
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

	public function fillProperties(array $values) : Main\Result
	{
		$propertyCollection = $this->internalOrder->getPropertyCollection();
		$result = new Main\Result();
		$changes = [];
		$filled = [];

		/** @var Sale\PropertyValue $property*/
		foreach ($propertyCollection as $property)
		{
			$propertyId = $property->getPropertyId();

			if ($propertyId === null || !array_key_exists($propertyId, $values)) { continue; }

			$value = $values[$propertyId];
			$sanitizedValue = $this->sanitizePropertyValue($property, $value);

			$property->setValue($sanitizedValue);

			if ($property->isChanged())
			{
				$changes[] = $propertyId;
			}

			$filled[] = $propertyId;
		}

		$result->setData([
			'CHANGES' => $changes,
			'FILLED' => $filled,
		]);

		return $result;
	}

	protected function sanitizePropertyValue(Sale\PropertyValue $property, $value)
	{
		//$value = $this->sanitizePropertyValueByType($property, $value);
		$value = $this->sanitizePropertyValueMultiple($property, $value);
		//$value = $this->sanitizePropertyValueOptions($property, $value);

		return $value;
	}

	protected function sanitizePropertyValueMultiple(Sale\PropertyValue $property, $value)
	{
		$propertyRow = $property->getProperty();
		$isPropertyMultiple = (isset($propertyRow['MULTIPLE']) && $propertyRow['MULTIPLE'] === 'Y');
		$isValueMultiple = is_array($value);

		if ($isPropertyMultiple === $isValueMultiple)
		{
			$result = $value;
		}
		else if ($isValueMultiple)
		{
			$result = $this->environment->getProperty()->joinPropertyMultipleValue($property, $value);
		}
		else if (!empty($value))
		{
			$result = [ $value ];
		}
		else
		{
			$result = null;
		}

		return $result;
	}

	public function setPersonType(int $personType) : Main\Result
	{
		return $this->internalOrder->setPersonTypeId($personType);
	}

	public function createShipment(int $deliveryId, float $price = null, int $storeId = null, array $data = null) : Main\Result
	{
		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $this->internalOrder->getShipmentCollection();

		$this->clearOrderShipment($shipmentCollection);
		$shipment = $this->buildOrderShipment($shipmentCollection, $deliveryId, $data);

		$this->fillShipmentPrice($shipment, $price);
		$this->fillShipmentBasket($shipment);
		$this->fillShipmentStore($shipment, $storeId);

		return new Main\Result();
	}

	protected function clearOrderShipment(Sale\ShipmentCollection $shipmentCollection) : void
	{
		/** @var Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$shipment->delete();
			}
		}
	}

	protected function buildOrderShipment(Sale\ShipmentCollection $shipmentCollection, $deliveryId, array $data = null)
	{
		$shipment = $shipmentCollection->createItem();

		if ((int)$deliveryId > 0)
		{
			$delivery = Sale\Delivery\Services\Manager::getObjectById($deliveryId);

			if ($delivery !== null)
			{
				$deliveryName = $delivery->getNameWithParent();
			}
			else
			{
				$deliveryName = 'Not found (' . $deliveryId . ')';
			}

			$shipment->setField('DELIVERY_ID', $deliveryId);
			$shipment->setField('DELIVERY_NAME', $deliveryName);
		}

		if (!empty($data))
		{
			$settableFields = array_flip($shipment::getAvailableFields());
			$settableData = array_intersect_key($data, $settableFields);

			$shipment->setFields($settableData);
		}

		return $shipment;
	}

	protected function fillShipmentPrice(Sale\Shipment $shipment, $price = null) : Sale\Result
	{
		if ($price !== null)
		{
			$result = $shipment->setFields([
				'CUSTOM_PRICE_DELIVERY' => 'Y',
				'BASE_PRICE_DELIVERY' => $price,
				'PRICE_DELIVERY' => $price,
			]);
		}
		else
		{
			$result = new Sale\Result();
		}

		return $result;
	}

	protected function fillShipmentStore(Sale\Shipment $shipment, int $storeId = null) : void
	{
		if ($storeId !== null && $storeId > 0)
		{
			$shipment->setStoreId($storeId);
		}
	}

	public function createPayment($paySystemId, $price = null, array $data = null) : Main\Result
	{
		$paymentCollection = $this->internalOrder->getPaymentCollection();

		$payment = $this->buildOrderPayment($paymentCollection, $paySystemId, $data);
		$this->fillPaymentPrice($payment, $price);

		return new Main\Result();
	}

	protected function buildOrderPayment(Sale\PaymentCollection $paymentCollection, $paySystemId, array $data = null) : Sale\Payment
	{
		$payment = $paymentCollection->createItem();

		if ((int)$paySystemId > 0)
		{
			$paySystem = Sale\PaySystem\Manager::getById($paySystemId);

			if ($paySystem !== false)
			{
				$paySystemName = $paySystem['NAME'];
			}
			else
			{
				$paySystemName = 'Not found (' . $paySystemId . ')';
			}

			$payment->setField('PAY_SYSTEM_ID', $paySystemId);
			$payment->setField('PAY_SYSTEM_NAME', $paySystemName);
		}

		if (!empty($data))
		{
			$settableFields = array_flip($payment::getAvailableFields());
			$settableData = array_intersect_key($data, $settableFields);

			$payment->setFields($settableData);
		}

		return $payment;
	}

	protected function fillPaymentPrice(Sale\Payment $payment, $price = null) : void
	{
		if ($price === null)
		{
			$orderPrice = $this->internalOrder->getPrice();
			$paymentsSum = $this->internalOrder->getPaymentCollection()->getSum();
			$selfSum = $payment->getSum();

			$price = $orderPrice - ($paymentsSum - $selfSum);
		}

		$payment->setField('SUM', $price);
	}

	public function add($externalId) : Main\Result
	{
		$result = new Main\Result();

		/*$this->syncOrderPrice();*/
		//$this->syncOrderPaymentSum();

		$orderResult = $this->internalOrder->save();

		if (!$orderResult->isSuccess())
		{
			$result->addErrors($orderResult->getErrors());
		}
		else
		{
			$orderId = $orderResult->getId();
			$orderExportId = $orderId;
			$orderAccountNumber = $this->getAccountNumber();

			if ($orderAccountNumber !== null)
			{
				$orderExportId = $orderAccountNumber;
			}

			[$paymentId, $paySystemId] = $this->getPayment();

			$result->setData([
				'ID' => $orderExportId,
				'INTERNAL_ID' => $orderId,
				'PAYMENT_ID' => $paymentId,
				'PAY_SYSTEM_ID' => $paySystemId
			]);
		}

		return $result;
	}

	public function getAccountNumber() : ?string
	{
		$accountNumber = (string)$this->internalOrder->getField('ACCOUNT_NUMBER');

		return $accountNumber !== '' ? $accountNumber : null;
	}

	protected function getPayment() : array
	{
		$paymentId = null;
		$paySystemId = null;

		$paymentCollection = $this->internalOrder->getPaymentCollection();

		/** @var Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if ($payment->isInner()) { continue; }

			$paymentId = $payment->getId();
			$paySystemId = $payment->getPaymentSystemId();
		}

		return [$paymentId, $paySystemId];
	}

	/*protected function syncOrderPrice()
	{
		$currentPrice = $this->internalOrder->getPrice();
		$calculatedPrice = $this->getCalculatedOrderPrice();

		if (Market\Data\Price::round($currentPrice) !== Market\Data\Price::round($calculatedPrice))
		{
			$this->internalOrder->setField('PRICE', $calculatedPrice);
		}
	}*/

	protected function syncOrderPaymentSum() : void
	{
		$paymentCollection = $this->internalOrder->getPaymentCollection();

		if ($paymentCollection)
		{
			$lastPayment = null;
			$orderSum = $this->internalOrder->getPrice();
			$paymentSum = 0;

			/** @var Sale\Payment $payment*/
			foreach ($paymentCollection as $payment)
			{
				$paymentSum += $payment->getSum();

				if (!$payment->isPaid() && !$payment->isInner())
				{
					$lastPayment = $payment;
				}
			}

			if (
				$lastPayment !== null
				&& $orderSum !== $paymentSum
			)
			{
				$newPaymentSum = $orderSum - ($paymentSum - $lastPayment->getSum());

				$payment->setField('SUM', $newPaymentSum);
			}
		}
	}

	public function getId()
	{
		return $this->internalOrder->getId();
	}

	public function setComment(string $value) : void
	{
		$this->internalOrder->setField('USER_DESCRIPTION', $value);
	}
}