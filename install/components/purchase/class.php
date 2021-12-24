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
	/** @var int */
	protected $userId;

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
				'code' => (string)$exception->getCode(),
				'message' => $exception->getMessage(),
				//'trace' => $exception->getTraceAsString()
			]);

			/*pr($exception->getMessage(), 1);
			die;*/

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

		return $request !== null ? (string)$request : 'view';
	}

	protected function callAction(string $action) : void
	{
		$action = trim($action);

		if ($action === '') { throw new Main\ArgumentException('action is empty'); }

		$method = $action . 'Action';

		if (!method_exists($this, $method)) { throw new Main\ArgumentException('unknown action'); }

		$this->{$method}();
	}

	protected function sendResponse(array $response) : void
	{
		$json = Main\Web\Json::encode($response);
		\CMain::FinalActions($json);
	}

	protected function getProductsAction() : void
	{
		$order = $this->getOrder();

		$order->initialize();

		$this->fillPersonType($order);
		$this->fillBasket($order);
		//$this->fillCoupon($order);

		$order->finalize();

		$basket = $order->getBasket();

		if ($basket->isEmpty())
		{
			throw new Main\ArgumentException('empty basket');
		}

		$result['amount'] = (string)$basket->getPrice();//number_format($basket->getPrice(), 2, '.', '');

		foreach ($basket as $basketItem)
		{
			if ($basketItem->getFinalPrice() <= 0) { continue; }

			$result['items'][] = $this->collectBasketItem($basketItem);
		}

		$this->sendResponse($result);
	}

	protected function collectBasketItem(\Bitrix\Sale\BasketItemBase $basketItem) : array
	{
		return [
			'id' => $basketItem->getProductId(),
			'count' => (string)$basketItem->getQuantity(),
			'label' => $basketItem->getField('NAME'),
			'amount' => (string)$basketItem->getFinalPrice() //number_format($basketItem->getFinalPrice(), 2, '.', '')
		];
	}

	protected function deliveryOptionsAction() : void
	{
		$result = [];

		/** @var TradingAction\Incoming\DeliveryOptions $request */
		$request = $this->makeRequest(TradingAction\Incoming\DeliveryOptions::class);
		$order = $this->getOrder();

		$order->initialize();

		$this->fillPersonType($order);
		$this->fillBasket($order);
		//$this->fillCoupon($order); //todo
		$this->fillLocation($order, $request->getAddress());

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
			'id'        => $calculationResult->getDeliveryId(),
			'label'     => $calculationResult->getServiceName(),
			'amount'    => (string)$calculationResult->getPrice(),
			'provider'  => 'custom', //todo
			'category'  => $calculationResult->getCategory(),
			'date'      => $calculationResult->getDateFrom()->getTimestamp()
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

		$order = $this->getOrder();

		$order->initialize();

		$this->fillPersonType($order);
		$this->fillBasket($order);
		//$this->fillCoupon($order);

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
			'id'        => [
				'deliveryId' => $calculationResult->getDeliveryId(), //todo пока что нельзя передавать дополнительные данные
				'storeId' => (int)$store['ID'],
				'locationId' => $locationId
			],
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
			]
		];
	}

	protected function couponAction() : void
	{
		$order = $this->getOrder();
		$this->fillBasket($order);
		$this->fillCoupon($order);
	}

	protected function orderAcceptAction() : void
	{
		/** @var TradingAction\Incoming\OrderAccept $request */
		$request = $this->makeRequest(TradingAction\Incoming\OrderAccept::class);
		$paySystemId = $request->getPaySystemId();

		$userId = $this->createUser($request);
		$order = $this->getOrder($userId);

		$order->initialize();

		$this->fillPersonType($order);
		$this->fillStatus($order);
		$this->fillProperties($order, $request);
		$this->fillBasket($order);

		$this->excludeProductsForBasket($order, $request);

		if ($request->getDeliveryType() === EntitySale\Delivery::PICKUP_TYPE)
		{
			$this->fillPickup($order, $request->getPickup());
		}
		else
		{
			$this->fillLocation($order, $request->getAddress());
			$this->fillDelivery($order, $request->getDelivery());
		}

		//$this->fillCoupon($order);

		$this->fillPaySystem($order, $paySystemId, $request->getOrderAmount());

		$order->finalize();

		$this->addOrder($order);
	}

	protected function fillPickup(EntityReference\Order $order, TradingAction\Incoming\OrderAccept\Pickup $pickup) : void
	{
		$this->setLocation($order, $pickup->getLocationId());

		$deliveryId = $pickup->getId();
		$price = $pickup->getAmount();
		$storeId = $pickup->getStoreId();

		if ((string)$deliveryId === '')
		{
			$deliveryId = $this->environment->getDelivery()->getEmptyDeliveryId();
		}

		$order->createShipment($deliveryId, $price, $storeId);
	}

	protected function fillBasket(EntityReference\Order $order) : void
	{
		/** @var TradingAction\Incoming\Product $request */
		$request = $this->makeRequest(TradingAction\Incoming\Product::class);
		$mode = $request->getMode();

		if ($mode === Pay\Injection\Behavior\Registry::ELEMENT)
		{
			$addProductResult = $order->addProduct($request->getProductId());
		}
		elseif (
			$mode === Pay\Injection\Behavior\Registry::BASKET
			|| $mode === Pay\Injection\Behavior\Registry::ORDER
		)
		{
			$addProductResult = $order->loadUserBasket();
		}
		else
		{
			throw new Main\ArgumentException('not found mode');
		}

		Exceptions\Facade::handleResult($addProductResult);
	}

	protected function excludeProductsForBasket(EntityReference\Order $order, TradingAction\Incoming\OrderAccept $request) : void
	{
		$products = $request->getItems()->getProducts();
		$basket = $order->getBasket();

		$order->finalize();

		/** @var \Bitrix\Sale\BasketItemBase $basketItem */
		foreach ($basket as $basketItem)
		{
			$productId = $basketItem->getProductId();

			if ($basketItem->getFinalPrice() <= 0) { continue; }

			if (!isset($products[$productId]))
			{
				$basketItem->delete();
			}
			else if ($products[$productId] > 0
				&& $products[$productId]!== $basketItem->getQuantity()
			)
			{
				$basketItem->setFieldNoDemand('QUANTITY', $products[$productId]);
			}
		}

		$order->initialize();
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

	protected function addOrder(EntityReference\Order $order) : void
	{
		$externalId = $order->getId();
		$saveResult = $order->add($externalId);

		Exceptions\Facade::handleResult($saveResult);

		$saveData = $saveResult->getData();

		if (!isset($saveData['ID']))
		{
			$errorMessage = 'TRADING_ACTION_ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET';//static::getLang('TRADING_ACTION_ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET');
			throw new Main\SystemException($errorMessage);
		}

		[$paymentId, $paySystemId] = $this->getPayment($saveData['ID']);

		$redirect = $this->getRedirectUrl($saveData['ID'], $paySystemId);

		$result = [
			'externalId' => $paymentId,
			'paySystemId' => $paySystemId,
			'redirect' => $redirect
		];

		$this->sendResponse($result);
	}

	protected function getRedirectUrl(int $orderId, int $paySystemId) : ?string
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
			$_SESSION['yabehaviorbackurl'] = $url;
		}

		return $result;
	}

	protected function getPayment(int $orderId) : array
	{
		$order = Sale\Order::load($orderId);

		if ($order === null) { return []; }

		$paymentId = null;
		$paySystemId = null;

		$paymentCollection = $order->getPaymentCollection();

		/** @var Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if ($payment->isInner()) { continue; }

			$paymentId = $payment->getId();
			$paySystemId = $payment->getPaymentSystemId();
		}

		return [$paymentId, $paySystemId];
	}

	protected function fillPaySystem(EntityReference\Order $order, int $paySystemId, float $price = null) : void
	{
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

	protected function createUser(TradingAction\Incoming\OrderAccept $request) : int
	{
		global $USER;

		$userId = $request->getUserId();

		if ($userId > 0)
		{
			$this->userId = $userId;

			return $this->userId;
		}

		$userData = $request->getUser();
		$allowAppendOrder = true;

		$user = $this->environment->getUserRegistry()->getUser([
			'EMAIL' => $userData->getEmail(),
			'PHONE' => $userData->getPhone(),
			'FIRST_NAME' => $userData->getFirstName(),
			'LAST_NAME' => $userData->getLastName(),
			'SECOND_NAME' => $userData->getSecondName(),
			'SITE_ID' => $this->setup->getSiteId(),
		]);

		if ($allowAppendOrder)
		{
			$this->userId = $user->getId();

			if ($this->userId !== null)
			{
				$USER->Authorize($this->userId);

				return $this->userId;
			}
		}

		/** @var Main\ORM\Data\AddResult $installResult */
		$installResult = $user->install();

		Exceptions\Facade::handleResult($installResult);

		$this->userId = $installResult->getId();

		$USER->Authorize($this->userId);

		return $this->userId;
	}

	protected function getOrder(int $userId = null) : EntityReference\Order
	{
		$requestUser = $this->request->get('userId') ?? $this->request->get('fUserId');
		$userId = $userId ?? (int)$requestUser;
		$siteId = $this->setup->getSiteId() ?? SITE_ID;

		return $this->environment->getOrderRegistry()->createOrder(
			$siteId,
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
		$this->setup->wakeupOptions();
		$this->setup->fill();

		$this->options = $this->setup->getOptions();
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