<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action as TradingAction;
use YandexPay\Pay\Trading\Settings as TradingSettings;
use YandexPay\Pay\Trading\Setup as TradingSetup;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Utils;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

Loc::loadMessages(__FILE__);

class Purchase extends \CBitrixComponent
{
	/** @var EntityReference\Environment */
	protected $environment;
	/** @var TradingSettings\Options */
	protected $options;
	/** @var TradingSetup\Model */
	protected $setup;
	/** @var array<int, string> $productIndex => $basketCode  */
	protected $basketMap = [];

	public function executeComponent(): void
	{
		try
		{
			$this->loadModules();
			$this->parseRequest();
			$this->bootstrap();

			$action = $this->resolveAction();
			$this->callAction($action);
		}
		catch (Main\SystemException $exception)
		{
			$this->sendResponse([
				'error' => [
					'code' => (string)$exception->getCode(),
					'message' => $exception->getMessage(),
				],
			]);

			// todo show error, may be loggers
		}
	}

	protected function parseRequest() : void
	{
		$this->request->addFilter(new Utils\JsonBodyFilter());
	}

	protected function makeRequest($className) : TradingAction\Incoming\Common
	{
		Assert::isSubclassOf($className, Pay\Reference\Common\Model::class);

		$data = $this->request->getPostList()->toArray();

		return new $className($data);
	}

	protected function resolveAction() : string
	{
		$request = $this->request->get('yapayAction');

		Assert::notNull($request, 'yapayAction');

		return (string)$request;
	}

	protected function callAction(string $action) : void
	{
		$action = trim($action);
		$method = $action . 'Action';

		if (!method_exists($this, $method)) { throw new Main\ArgumentException('unknown action'); }

		$this->{$method}();
	}

	protected function sendResponse(array $response) : void
	{
		global $APPLICATION;

		$APPLICATION->RestartBuffer();

		$json = Main\Web\Json::encode($response);
		\CMain::FinalActions($json);
	}

	protected function getProductsAction() : void
	{
		$userId = $this->searchUser();
		$order = $this->getOrder($userId);

		/** @var TradingAction\Incoming\Product $request */
		$request = $this->makeRequest(TradingAction\Incoming\Product::class);

		$order->initialize();

		$this->fillPersonType($order);
		$this->wakeUpBasket($order, $request);

		$order->finalize();

		// collect response

		$result['amount'] = (string)$order->getOrderPrice();

		foreach ($order->getOrderableItems() as $basketCode)
		{
			$itemResult = $order->getBasketItemData($basketCode);
			$itemData = $itemResult->getData();

			Exceptions\Facade::handleResult($itemResult);
			Assert::notNull($itemData['QUANTITY'], '$itemData[QUANTITY]');

			if ($itemData['QUANTITY'] <= 0) { continue; }

			$result['items'][] = $this->collectBasketItem($itemData);
		}

		$this->sendResponse($result);
	}

	protected function collectBasketItem(array $basketItem) : array
	{
		return [
			'id' => $basketItem['PRODUCT_ID'],
			'count' => (string)$basketItem['QUANTITY'],
			'label' => (string)$basketItem['NAME'],
			'amount' => (string)($basketItem['PRICE'] * $basketItem['QUANTITY']),
			'basketId' => $basketItem['BASKET_ID'] ?? null,
			'props' => $basketItem['PROPS'],
		];
	}

	protected function deliveryOptionsAction() : void
	{
		$result = [];

		$userId = $this->searchUser();
		$order = $this->getOrder($userId);

		/** @var TradingAction\Incoming\DeliveryOptions $request */
		$request = $this->makeRequest(TradingAction\Incoming\DeliveryOptions::class);

		$order->initialize();

		$this->fillPersonType($order);
		$this->fillLocation($order, $request->getAddress());
		$this->fillBasket($order, $request->getItems());

		$order->finalize();

		$deliveries = $this->restrictedDeliveries($order);
		$deliveries = $this->filterDeliveryByType($deliveries, EntitySale\Delivery::DELIVERY_TYPE);

		foreach ($deliveries as $deliveryId)
		{
			if (!$this->isDeliveryCompatible($order, $deliveryId)) { continue; }

			$calculationResult = $this->calculateDelivery($order, $deliveryId);

			if (!$calculationResult->isSuccess()) { continue; }

			$result[] = $this->collectDeliveryOption($calculationResult);
		}

		$this->sendResponse($result);
	}

	protected function collectDeliveryOption(EntityReference\Delivery\CalculationResult $calculationResult) : array
	{
		return [
			'id'        => (string)$calculationResult->getDeliveryId(),
			'label'     => $calculationResult->getServiceName(),
			'amount'    => (string)$calculationResult->getPrice(),
			'provider'  => 'custom', //todo
			'category'  => $calculationResult->getCategory(),
			'date'      => $calculationResult->getDateFrom()->getTimestamp(),
		];
	}

	protected function restrictedDeliveries(EntityReference\Order $order) : array
	{
		$result = [];
		$deliveryService = $this->environment->getDelivery();
		$compatibleIds = $deliveryService->getRestricted($order);

		if (empty($compatibleIds)) { return $result; }

		if ($this->options->isDeliveryStrict())
		{
			$deliveryOptions = $this->options->getDeliveryOptions();
			$configuredIds = $deliveryOptions->getServiceIds();

			$result = array_intersect($compatibleIds, $configuredIds);
		}
		else
		{
			$result = $compatibleIds;
		}

		return $result;
	}

	protected function isDeliveryCompatible(EntityReference\Order $order, int $deliveryId) : bool
	{
		return $this->environment->getDelivery()->isCompatible($deliveryId, $order);
	}

	protected function calculateDelivery(EntityReference\Order $order, int $deliveryId) : EntityReference\Delivery\CalculationResult
	{
		return $this->environment->getDelivery()->calculate($deliveryId, $order);
	}

	protected function getPickupStores(EntityReference\Order $order, int $deliveryId) : array
	{
		return $this->environment->getDelivery()->getPickupStores($deliveryId, $order);
	}

	protected function filterDeliveryByType(array $deliveryIds, string $type) : array
	{
		if (empty($deliveryIds)) { return []; }

		$result = [];

		$deliveryOptions = $this->options->getDeliveryOptions();
		$deliveryService = $this->environment->getDelivery();

		foreach ($deliveryIds as $deliveryId)
		{
			$service = $deliveryOptions->getItemByServiceId($deliveryId);

			if ($service !== null)
			{
				$typeOption = $service->getType();
			}
			else
			{
				$typeOption = $deliveryService->suggestDeliveryType($deliveryId);
			}

			if ($typeOption !== $type) { continue; }

			$result[] = $deliveryId;
		}

		return $result;
	}

	protected function pickupOptionsAction() : void
	{
		$result = [];

		$userId = $this->searchUser();
		$order = $this->getOrder($userId);

		/** @var TradingAction\Incoming\PickupOptions $request */
		$request = $this->makeRequest(TradingAction\Incoming\PickupOptions::class);

		$order->initialize();

		$this->fillPersonType($order);
		$this->fillBasket($order, $request->getItems());

		$order->finalize();

		$deliveries = $this->restrictedDeliveries($order);
		$deliveries = $this->filterDeliveryByType($deliveries, EntitySale\Delivery::PICKUP_TYPE);

		foreach ($deliveries as $deliveryId)
		{
			if (!$this->isDeliveryCompatible($order, $deliveryId)) { continue; }

			$allStores = $this->getPickupStores($order, $deliveryId);
			//$storesByLocation = $this->groupStoresByLocation($allStores); //todo, need group?
			foreach ($allStores as $locationId => $stores)
			{
				$this->setLocation($order, $locationId);

				$calculationResult = $this->calculateDelivery($order, $deliveryId);

				if (!$calculationResult->isSuccess()) { continue; }

				foreach ($stores as $store)
				{
					$result[] = $this->collectPickupOption($store, $calculationResult, $locationId);
				}
			}
		}

		$this->sendResponse($result);
	}

	protected function collectPickupOption(array $store, EntityReference\Delivery\CalculationResult $calculationResult, int $locationId = null) : array
	{
		return [
			'id' => Main\Web\Json::encode([
				'deliveryId' => $calculationResult->getDeliveryId(),
				'storeId' => (int)$store['ID'],
				'locationId' => $locationId,
			]),
			'label'     => $store['TITLE'],
			'provider'  => 'pickpoint', //todo
			'address'   => $store['ADDRESS'],
			'deliveryDate' => $calculationResult->getDateFrom()->getTimestamp(),
			'amount'    => (string)$calculationResult->getPrice(),
			//'storagePeriod' => 3, // todo
			'info' => [
				'contacts' => [$store['PHONE']],
				'tripDescription' => $store['DESCRIPTION'] . PHP_EOL . $store['SCHEDULE'],
			],
			'coordinates' =>  [
				'latitude' => (float)$store['GPS_N'],
				'longitude' => (float)$store['GPS_S'],
			],
		];
	}

	protected function couponAction() : void
	{
		$order = $this->getOrder();
		//$this->fillBasket($order);
		$this->fillCoupon($order);
	}

	protected function orderAcceptAction() : void
	{
		/** @var TradingAction\Incoming\OrderAccept $request */
		$request = $this->makeRequest(TradingAction\Incoming\OrderAccept::class);

		$userId = $this->createUser($request);
		$order = $this->getOrder($userId);

		$order->initialize();

		$this->fillPersonType($order);
		$this->fillStatus($order);
		$this->fillProperties($order, $request);

		if ($request->getDeliveryType() === EntitySale\Delivery::PICKUP_TYPE)
		{
			$this->setLocation($order, $request->getPickup()->getLocationId());
			$this->fillBasket($order, $request->getItems());
			$this->fillPickup($order, $request->getPickup());
		}
		else
		{
			$this->fillLocation($order, $request->getAddress());
			$this->fillBasket($order, $request->getItems());
			$this->fillDelivery($order, $request->getDelivery());
		}

		$this->fillPaySystem($order, $request);

		$order->finalize();

		$this->check($order, $request);

		$saveOrderData = $this->addOrder($order);

		$redirect = $this->getRedirectUrl($saveOrderData['ID'], $saveOrderData['PAY_SYSTEM_ID']);

		$result = [
			'externalId' => $saveOrderData['PAYMENT_ID'],
			'paySystemId' => $saveOrderData['PAY_SYSTEM_ID'],
			'redirect' => $redirect,
		];

		$this->sendResponse($result);
	}

	protected function check(EntityReference\Order $order, TradingAction\Incoming\OrderAccept $request) : void
	{
		$this->checkBasket($order, $request->getItems());
		$this->checkDelivery($order, $request);
		$this->checkTotal($order, $request);
	}

	protected function checkTotal(EntityReference\Order $order, TradingAction\Incoming\OrderAccept $request) : void
	{
		$orderPrice = $order->getOrderPrice();
		$requestPrice = $request->getOrderAmount();

		if (Pay\Data\Price::round($orderPrice) !== Pay\Data\Price::round($requestPrice))
		{
			$message = $this->getLang('ORDER_PRICE_NOT_MATCH', [
				'#REQUEST_PRICE#' => $requestPrice,
				'#ORDER_PRICE#' => $orderPrice
			]);
			throw new Main\SystemException($message);
		}
	}

	protected function checkDelivery(EntityReference\Order $order, TradingAction\Incoming\OrderAccept $request) : void
	{
		$delivery = $request->getDeliveryType() === EntitySale\Delivery::PICKUP_TYPE ? $request->getPickup() : $request->getDelivery();
		$shipmentPrice = $order->getShipmentPrice($delivery->getId());

		Assert::notNull($shipmentPrice, '$shipmentPrice');

		if (Pay\Data\Price::round($shipmentPrice) !== Pay\Data\Price::round($delivery->getAmount()))
		{
			$priceResult = $order->setShipmentPrice($delivery->getId(), $delivery->getAmount());
			Exceptions\Facade::handleResult($priceResult);
		}
	}

	protected function checkBasket(EntityReference\Order $order, TradingAction\Incoming\Items $items) : void
	{
		/** @var TradingAction\Incoming\Item $item */
		foreach ($items as $index => $item)
		{
			$basketCode = $this->basketMap[$index] ?? null;

			Assert::notNull($basketCode, '$basketMap[$index]');

			$basketResult = $order->getBasketItemData($basketCode);
			$basketData = $basketResult->getData();

			Exceptions\Facade::handleResult($basketResult);

			Assert::notNull($basketData['QUANTITY'], '$basketData[QUANTITY]');
			Assert::notNull($basketData['PRICE'], '$basketData[PRICE]');

			$productPrice = $basketData['PRICE'] * $basketData['QUANTITY'];

			if (Pay\Data\Price::round($productPrice) !== Pay\Data\Price::round($item->getAmount()))
			{
				$priceResult = $order->setBasketItemPrice($basketCode, $item->getAmount());
				Exceptions\Facade::handleResult($priceResult);
			}

			if (Pay\Data\Quantity::round($basketData['QUANTITY']) !== Pay\Data\Quantity::round($item->getQuantity()))
			{
				$quantityResult = $order->setBasketItemQuantity($basketCode, $item->getQuantity());
				Exceptions\Facade::handleResult($quantityResult);
			}
		}
	}

	protected function fillPickup(EntityReference\Order $order, TradingAction\Incoming\OrderAccept\Pickup $pickup) : void
	{
		$deliveryId = $pickup->getId();
		$price = $pickup->getAmount();
		$storeId = $pickup->getStoreId();

		if ((string)$deliveryId === '')
		{
			$deliveryId = $this->environment->getDelivery()->getEmptyDeliveryId();
		}

		$order->createShipment($deliveryId, $price, $storeId);
	}

	protected function wakeUpBasket(EntityReference\Order $order, TradingAction\Incoming\Product $request) : void
	{
		$mode = $request->getMode();

		if ($mode === Pay\Injection\Behavior\Registry::ELEMENT)
		{
			$productId = $request->getProductId();
			$offerId = $this->environment->getProduct()->resolveOffer($productId);

			$order->initEmptyBasket();

			$basketData = $this->getProductBasketData($offerId);
			$addResult = $order->addProduct($offerId, 1, $basketData);
		}
		elseif (
			$mode === Pay\Injection\Behavior\Registry::BASKET
			|| $mode === Pay\Injection\Behavior\Registry::ORDER
		)
		{
			$addResult = $order->initUserBasket();
		}
		else
		{
			throw new Main\ArgumentException('not found mode');
		}

		Exceptions\Facade::handleResult($addResult);
	}

	protected function getProductBasketData(int $productId) : array
	{
		$environmentProduct = $this->environment->getProduct();
		$fewData = $environmentProduct->getBasketData([$productId]);
		$itemData = $fewData[$productId] ?? null;

		Assert::notNull($itemData, '$enviromentProduct->getBasketData()');

		if (isset($itemData['ERROR']))
		{
			throw new Main\SystemException($itemData['ERROR']);
		}

		return $itemData;
	}

	protected function fillBasket(EntityReference\Order $order, TradingAction\Incoming\Items $products) : void
	{
		[$exists, $new] = $this->splitBasketAlreadyExists($products);

		if (!empty($exists))
		{
			$order->initUserBasket();

			[$notFound, $needDelete] = $this->syncBasketExistProducts($order, $exists);

			$this->deleteBasketProducts($order, $needDelete);
			$this->addBasketNewProducts($order, $notFound);
		}
		else
		{
			$order->initEmptyBasket();
		}

		$this->addBasketNewProducts($order, $new);
	}

	protected function splitBasketAlreadyExists(TradingAction\Incoming\Items $products) : array
	{
		$exists = [];
		$new = [];

		/** @var TradingAction\Incoming\Item $product */
		foreach ($products as $index => $product)
		{
			if ((int)$product->getBasketId() > 0)
			{
				$exists[$index] = $product;
			}
			else
			{
				$new[$index] = $product;
			}
		}

		return [$exists, $new];
	}

	protected function syncBasketExistProducts(EntityReference\Order $order, array $products) : array
	{
		$productCodes = array_map(static function(TradingAction\Incoming\Item $item) { return $item->getBasketId(); }, $products);
		$existsCodes = $order->getOrderableItems();
		$existsMap = array_flip($existsCodes);
		$notFound = [];
		$needDelete = array_diff($existsCodes, $productCodes);

		/** @var TradingAction\Incoming\Item $product */
		foreach ($products as $index => $product)
		{
			$basketCode = $product->getBasketId();

			if (!isset($existsMap[$basketCode]))
			{
				$notFound[$index] = $product;
				continue;
			}

			$quantityResult = $order->setBasketItemQuantity($basketCode, $product->getQuantity());

			Exceptions\Facade::handleResult($quantityResult);

			$this->basketMap[$index] = $basketCode;
		}

		return [$notFound, $needDelete];
	}

	protected function deleteBasketProducts(EntityReference\Order $order, array $basketCodes) : void
	{
		foreach ($basketCodes as $basketCode)
		{
			$result = $order->deleteBasketItem($basketCode);
			Exceptions\Facade::handleResult($result);
		}
	}

	protected function addBasketNewProducts(EntityReference\Order $order, array $products) : void
	{
		/** @var TradingAction\Incoming\Item $product */
		foreach ($products as $index => $product)
		{
			$productId = $product->getId();
			$quantity = $product->getQuantity();
			$data = [
				'PROPS' => $product->getProps(),
			];

			$addResult = $order->addProduct($productId, $quantity, $data);
			$addData = $addResult->getData();

			Exceptions\Facade::handleResult($addResult);
			Assert::notNull($addData['BASKET_CODE'], '$addData[BASKET_CODE]');

			$this->basketMap[$index] = $addData['BASKET_CODE'];
		}
	}

	protected function fillCoupon(EntityReference\Order $order) : void
	{
		/** @var \YandexPay\Pay\Trading\Action\Request\Coupon $requestCoupon */
		$requestCoupon = $this->getRequestCoupon();
		$coupon = $requestCoupon->getCoupon();// ?? 'SL-HO25W-JD3ASTQ';

		if ($coupon !== null)
		{
			$couponResult = $order->applyCoupon($coupon);
			Exceptions\Facade::handleResult($couponResult);
		}
	}

	protected function addOrder(EntityReference\Order $order) : array
	{
		global $USER;

		$externalId = $order->getId();
		$saveResult = $order->add($externalId);

		Exceptions\Facade::handleResult($saveResult);

		$saveData = $saveResult->getData();

		if (!isset($saveData['ID']))
		{
			$errorMessage = $this->getLang('ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET');
			throw new Main\SystemException($errorMessage);
		}

		if (!$USER->IsAuthorized())
		{
			if (!is_array($_SESSION['SALE_ORDER_ID']))
			{
				$_SESSION['SALE_ORDER_ID'] = [];
			}

			$_SESSION['SALE_ORDER_ID'][] = $saveData['INTERNAL_ID'];
		}

		return $saveData;
	}

	protected function getRedirectUrl(string $orderId, int $paySystemId) : ?string
	{
		$result = null;

		$options = $this->options;
		$isCashPaySystem = ($options->getPaymentCash() === $paySystemId);
		$url = Utils\Url::absolutizePath($this->options->getSuccessUrl()) . '?ORDER_ID=' . $orderId;

		if ($isCashPaySystem)
		{
			$result = $url;
		}
		else
		{
			unset($_SESSION['yabackurl'], $_SESSION['yabehaviorbackurl']);
			$_SESSION['yabehaviorbackurl'] = $url;
		}

		return $result;
	}

	protected function fillPaySystem(EntityReference\Order $order, TradingAction\Incoming\OrderAccept $request) : void
	{
		$paySystemId = $request->getPaySystemId();
		$price = $request->getOrderAmount();

		if ($paySystemId > 0)
		{
			$order->createPayment($paySystemId, $price);
		}
	}

	protected function fillDelivery(EntityReference\Order $order, TradingAction\Incoming\OrderAccept\Delivery $delivery) : void
	{
		$deliveryId = $delivery->getId();
		$price = $delivery->getAmount();

		if ((string)$deliveryId === '')
		{
			$deliveryId = $this->environment->getDelivery()->getEmptyDeliveryId();
		}

		$order->createShipment($deliveryId, $price);
	}

	protected function filterPickup(array $pickup) : array
	{
		/**
		 * @var TradingAction\Request\Address $directions
		 * @var $northeast TradingAction\Request\Address\Coordinates
		 * @var $southwest TradingAction\Request\Address\Coordinates
		 */
		$directions = $this->getRequestAddress();
		$northeast = $directions->getNortheast();
		$southwest = $directions->getSouthwest();

		return array_values(array_filter($pickup, static function ($value) use ($northeast, $southwest){
			return (
				$value['coordinates']['latitude'] >= $southwest->getLat()
				&& $value['coordinates']['latitude'] <= $northeast->getLat()
				&& $value['coordinates']['longitude'] >= $southwest->getLon()
				&& $value['coordinates']['longitude'] <= $northeast->getLon()
			);
		}));
	}

	protected function fillStatus(EntityReference\Order $order) : void
	{
		$statusResult = $order->setStatus(Sale\OrderStatus::getInitialStatus());

		Exceptions\Facade::handleResult($statusResult);
	}

	protected function searchUser() : ?int
	{
		global $USER;

		return $USER->IsAuthorized() ? (int)$USER->GetID() : null;
	}

	protected function createUser(TradingAction\Incoming\OrderAccept $request) : int
	{
		global $USER;

		if ($USER->IsAuthorized())
		{
			return (int)$USER->GetID();
		}

		$userData = $request->getUser();

		$user = $this->environment->getUserRegistry()->getUser([
			'EMAIL' => $userData->getEmail(),
			'PHONE' => $userData->getPhone(),
			'FIRST_NAME' => $userData->getFirstName(),
			'LAST_NAME' => $userData->getLastName(),
			'SECOND_NAME' => $userData->getSecondName(),
			'SITE_ID' => $this->setup->getSiteId(),
		]);

		$userId = $user->getId(); //todo allowAppendOrder options

		if ($userId !== null)
		{
			return $userId;
		}

		/** @var Main\ORM\Data\AddResult $installResult */
		$installResult = $user->install();

		Exceptions\Facade::handleResult($installResult);

		$userId = $installResult->getId();
		$USER->Authorize($userId);

		return $userId;
	}

	protected function getOrder(int $userId = null) : EntityReference\Order
	{
		return $this->environment->getOrderRegistry()->createOrder(
			$this->setup->getSiteId(),
			$userId,
			$this->request->get('currency')
		);
	}

	protected function fillLocation(EntityReference\Order $order, TradingAction\Incoming\Address $address) : void
	{
		$locationService = $this->environment->getLocation();
		$locationId = $locationService->getLocation($address->getFields());
		$meaningfulValues = $locationService->getMeaningfulValues($locationId);

		$this->setLocation($order, $locationId);

		if (!empty($meaningfulValues))
		{
			$this->setMeaningfulPropertyValues($order, $meaningfulValues);
		}
	}

	protected function setLocation(EntityReference\Order $order, $locationId) : void
	{
		$orderResult = $order->setLocation($locationId);

		Exceptions\Facade::handleResult($orderResult);
	}

	protected function fillProperties(EntityReference\Order $order, TradingAction\Incoming\OrderAccept $request) : void
	{
		$this->fillBuyerProperties($order, $request);

		$deliveryType = $request->getDeliveryType();

		if ($deliveryType !== EntitySale\Delivery::PICKUP_TYPE)
		{
			$this->fillAddress($order, $request);
			$this->fillComment($order, $request);
		}
	}

	protected function fillComment(EntityReference\Order $order, TradingAction\Incoming\OrderAccept $request): void
	{
		$address = $request->getAddress();
		$comment = $address->getComment();

		if ((string)$comment !== '')
		{
			$order->setComment($comment);
		}
	}

	protected function fillBuyerProperties(EntityReference\Order $order, TradingAction\Incoming\OrderAccept $request) : void
	{
		$buyer = $request->getUser();
		$values = $buyer->getMeaningfulValues();
		$this->setMeaningfulPropertyValues($order, $values);
	}

	protected function fillAddress(EntityReference\Order $order, TradingAction\Incoming\OrderAccept $request) : void
	{
		$address = $request->getAddress();
		$propertyValues = $this->getAddressProperties($address);
		$this->setMeaningfulPropertyValues($order, $propertyValues);
	}

	protected function fillPersonType(EntityReference\Order $order) : void
	{
		Assert::notNull($this->setup->getPersonTypeId(), 'personal type');

		$personTypeResult = $order->setPersonType($this->setup->getPersonTypeId());

		Exceptions\Facade::handleResult($personTypeResult);
	}

	protected function setMeaningfulPropertyValues(EntityReference\Order $order, $values) : void
	{
		$propertyValues = $this->combineMeaningfulPropertyValues($values);

		if (!empty($propertyValues))
		{
			$fillResult = $order->fillProperties($propertyValues);
			Exceptions\Facade::handleResult($fillResult);
		}
	}

	protected function combineMeaningfulPropertyValues($values) : array
	{
		$options = $this->options;
		$propertyValues = [];

		foreach ($values as $name => $value)
		{
			$propertyId = (string)$options->getProperty($name);

			if ($propertyId === '') { continue; }

			if (!isset($propertyValues[$propertyId]))
			{
				$propertyValues[$propertyId] = $value;
			}
			else
			{
				if (!is_array($propertyValues[$propertyId]))
				{
					$propertyValues[$propertyId] = [
						$propertyValues[$propertyId],
					];
				}

				if (is_array($value))
				{
					$propertyValues[$propertyId] = array_merge($propertyValues[$propertyId], $value);
				}
				else
				{
					$propertyValues[$propertyId][] = $value;
				}
			}
		}

		return $propertyValues;
	}

	protected function getAddressProperties(TradingAction\Incoming\Address $address) : array
	{
		return [
			'ZIP' => $address->getMeaningfulZip(),
			'CITY' => $address->getMeaningfulCity(),
			'ADDRESS' => $address->getMeaningfulAddress(),
			'LAT' => $address->getLat(),
			'LON' => $address->getLon(),
		];
	}

	protected function getRequestCoupon() : \YandexPay\Pay\Reference\Common\Model
	{
		$coupon = $this->request->get('coupon');

		return TradingAction\Request\Coupon::initialize(['coupon' => $coupon]);
	}

	protected function loadModules(): void
	{
		$requiredModules = $this->getRequiredModules();

		foreach ($requiredModules as $requiredModule)
		{
			if (!Main\Loader::includeModule($requiredModule))
			{
				$message = $this->getLang('MODULE_NOT_INSTALLED', [ '#MODULE_ID#' => $requiredModule ]);

				throw new Main\SystemException($message);
			}
		}
	}

	protected function getRequiredModules(): array
	{
		return [
			'yandexpay.pay',
			'sale',
		];
	}

	protected function bootstrap() : void
	{
		$this->environment = EntityRegistry::getEnvironment();

		$this->setup = $this->loadSetup();
		$this->setup->fill();

		$this->options = $this->setup->wakeupOptions();
	}

	protected function loadSetup() : TradingSetup\Model
	{
		return TradingSetup\Model::wakeUp(['ID' => $this->request->get('setupId')]);
	}

	protected function getLang(string $code, $replace = null, $language = null): string
	{
		return Main\Localization\Loc::getMessage('YANDEX_PAY_TRADING_CART_' . $code, $replace, $language);
	}
}