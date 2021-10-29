<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action as TradingAction;
use YandexPay\Pay\Trading\Settings as TradingSettings;
use YandexPay\Pay\Trading\Setup as TradingSetup;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;
use YandexPay\Pay\Utils\JsonBodyFilter;

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
			echo Main\Web\Json::encode([
				'code' => (string)$exception->getCode(),
				'message' => $exception->getMessage(),
			]);

			/*pr($exception->getMessage(), 1);
			die;*/

			// todo show error
		}
	}

	protected function parseRequest() : void
	{
		$this->request->addFilter(new JsonBodyFilter());
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

	protected function deliveryOptionsAction() : void
	{
		$order = $this->getOrder();

		$this->fillPersonType($order);
		$this->fillBasket($order);
		$this->fillLocation($order);

		$calculatedDeliveries = $this->calculateDeliveries($order, 'DELIVERY');

		echo Main\Web\Json::encode($calculatedDeliveries);
	}

	protected function pickupOptionsAction() : void
	{
		$result = [];

		$order = $this->getOrder();

		$this->fillPersonType($order);
		$this->fillBasket($order);
		$this->fillLocation($order);

		$calculatedDeliveries = $this->calculateDeliveries($order, 'PICKUP');
		echo '<pre>';
		print_r($calculatedDeliveries);
		echo '</pre>';

		foreach ($calculatedDeliveries as $pickup)
		{
			foreach ($pickup['stores'] as $store)
			{
				$result[] = [
					'id'        => $store['ID'],
					'label'     => $store['TITLE'],
					'provider'  => 'custom',
					'address'   => $store['ADDRESS'],
					'date'      => '', //todo
					'amount'    => $pickup['amount'],
					'info'      => [
						'schedule'      => $store['SCHEDULE'],
						'contacts'      => $store['PHONE'],
						'description'   => $store['DESCRIPTION'],
					],
				];
			}
		}


		echo '<pre>';
		print_r($result);
		echo '</pre>';
		die;
		//$this->environment->getDelivery()->


	}

	protected function fillBasket(EntityReference\Order $order, bool $isStrict = false) : void
	{
		/** @var TradingAction\Request\Mode $modeRequest */
		$modeRequest = $this->getRequestMode();

		if ($modeRequest->isProduct())
		{
			$addProductResult = $order->addProduct($this->request->get('productId'));
		}
		elseif ($modeRequest->isCart())
		{
			$addProductResult = $order->loadUserBasket();

			if ($isStrict)
			{
				$this->excludeProducts($order);
			}
		}
		else
		{
			throw new Main\ArgumentException('not found mode');
		}

		Exceptions\Facade::handleResult($addProductResult);
	}

	protected function excludeProducts(EntityReference\Order $order) : void
	{
		/** @var TradingAction\Request\Order $orderRequest */
		$orderRequest = $this->getRequestOrder();
		$basket = $order->getBasket();

		/** @var TradingAction\Request\Order\Items $itemsRequest */
		$itemsRequest = $orderRequest->getItems();
		$products = $itemsRequest->getProducts();

		/** @var \Bitrix\Sale\BasketItemBase $basketItem */
		foreach ($basket as $basketItem)
		{
			$productId = $basketItem->getProductId();

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
	}

	protected function couponAction() : void
	{
		$order = $this->getOrder();
		$this->fillBasket($order);
		$this->fillCoupon($order);
	}

	protected function fillCoupon(EntityReference\Order $order) : void
	{
		/** @var \YandexPay\Pay\Trading\Action\Request\Coupon $coupon */
		$coupon = $this->getRequestCoupon();
		$couponResult = $order->applyCoupon($coupon->getCoupon());

		Exceptions\Facade::handleResult($couponResult);
	}

	protected function orderAcceptAction() : void
	{
		$userId = $this->createUser();
		$order = $this->getOrder($userId);

		$this->fillPersonType($order);
		$this->fillStatus($order);
		$this->fillProperties($order);
		$this->fillLocation($order);
		$this->fillBasket($order, true);
		$this->fillDelivery($order);
		$this->fillPaySystem($order);

		$this->addOrder($order);


		//



		//$this->fillLocation($order);
		//$this->fillAddress($order);
		//$this->fillDelivery();




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

		$result = [
			'externalId' => $this->getPaymentId($saveData['ID']),
		];

		echo Main\Web\Json::encode($result);
	}

	protected function getPaymentId(int $orderId) : ?int
	{
		$result = null;

		$order = \Bitrix\Sale\Order::load($orderId);

		if ($order === null) { return null; }

		$paymentCollection = $order->getPaymentCollection();

		/** @var \Bitrix\Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if ($payment->isInner()) { continue; }

			$result = $payment->getId();
		}

		return $result;
	}

	protected function fillPaySystem(EntityReference\Order $order) : void
	{
		/** @var TradingAction\Request\Payment $requestPayment */
		$requestPayment = $this->getRequestPayment();

		$paySystemId = (int)$this->request->get('paySystemId');

		if ($requestPayment->isPaymentCash())
		{
			$paySystemId = $this->options->getPaymentCash() ?? $paySystemId;
		}

		if ($paySystemId > 0)
		{
			$order->createPayment($paySystemId);
		}
	}

	protected function fillDelivery(EntityReference\Order $order) : void
	{
		[$deliveryId, $price] = $this->resolveDelivery();

		if ((string)$deliveryId !== '')
		{
			$order->createShipment($deliveryId, $price);
		}
	}

	protected function resolveDelivery() : array
	{
		/** @var TradingAction\Request\Delivery $deliveryRequest */
		$deliveryRequest = $this->getRequestDelivery();
		$price = null;

		if ($deliveryRequest->getId() !== null)
		{
			$deliveryId = $deliveryRequest->getId();
			$price = $deliveryRequest->getAmount();
		}
		else
		{
			$deliveryId = $this->environment->getDelivery()->getEmptyDeliveryId();
		}

		return [$deliveryId, $price];
	}

	protected function fillStatus(EntityReference\Order $order) : void
	{
		$statusResult = $order->setStatus(Sale\OrderStatus::getInitialStatus());

		Exceptions\Facade::handleResult($statusResult);
	}

	protected function createUser() : int
	{
		global $USER;

		$userId = (int)$this->request->get('userId');

		if ($userId > 0)
		{
			$this->userId = $userId;

			return $this->userId;
		}

		/** @var TradingAction\Request\User $userData */
		$userData = $this->getRequestContact();
		$allowAppendOrder = true;

		$user = $this->environment->getUserRegistry()->getUser([
			'EMAIL' => $userData->getEmail(),
			'PHONE' => $userData->getPhone(),
			'FIRST_NAME' => $userData->getFirstName(),
			'LAST_NAME' => $userData->getLastName(),
			'SECOND_NAME' => $userData->getSecondName(),
			'SITE_ID' => $this->request->get('siteId'),
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
		$userId = $userId ?? (int)$this->request->get('fUserId');
		$siteId = $this->request->get('siteId') ?? SITE_ID;

		return $this->environment->getOrderRegistry()->createOrder(
			$siteId,
			$userId,
			$this->request->get('currency')
		);
	}

	protected function fillLocation(EntityReference\Order $order) : void
	{
		$address = $this->getRequestAddress();
		$locationService = $this->environment->getLocation();
		$locationId = $locationService->getLocation($address->getFields());

		$meaningfulValues = $locationService->getMeaningfulValues($locationId);

		$orderResult = $order->setLocation($locationId);

		Exceptions\Facade::handleResult($orderResult);

		if (!empty($meaningfulValues))
		{
			$this->setMeaningfulPropertyValues($order, $meaningfulValues);
		}
	}

	protected function fillProperties(EntityReference\Order $order) : void
	{
		$this->fillBuyerProperties($order);
		$this->fillAddress($order);
		$this->fillComment($order);
	}

	protected function fillComment(EntityReference\Order $order): void
	{
		/** @var TradingAction\Request\Address $address */
		$address = $this->getRequestAddress();
		$comment = (string)$address->getComment();

		if ($comment !== '')
		{
			$order->setComment($address->getComment());
		}
	}

	protected function fillBuyerProperties(EntityReference\Order $order) : void
	{
		/** @var TradingAction\Request\User $buyer */
		$buyer = $this->getRequestContact();
		$values = $buyer->getMeaningfulValues();
		$this->setMeaningfulPropertyValues($order, $values);
	}

	protected function fillAddress(EntityReference\Order $order) : void
	{
		/** @var TradingAction\Request\Address $address */
		$address = $this->getRequestAddress();
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

	public function getAddressProperties(TradingAction\Request\Address $address) : array
	{
		/** @var TradingAction\Request\Address\Coordinates $coordinates */
		$coordinates = $address->getCoordinates();

		return [
			'ZIP' => $address->getMeaningfulZip(),
			'CITY' => $address->getMeaningfulCity(),
			'ADDRESS' => $address->getMeaningfulAddress(),
			'LAT' => $coordinates->getLat(),
			'LON' => $coordinates->getLon(),
		];
	}

	protected function getRequestCoupon() : \YandexPay\Pay\Reference\Common\Model
	{
		$coupon = $this->request->get('coupon');

		return TradingAction\Request\Coupon::initialize(['coupon' => $coupon]);
	}

	protected function getRequestContact() : \YandexPay\Pay\Reference\Common\Model
	{
		$contact = $this->request->get('contact');

		Assert::notNull($contact, 'contact');
		Assert::isArray($contact, 'contact');

		return TradingAction\Request\User::initialize($contact);
	}

	protected function getRequestAddress() : \YandexPay\Pay\Reference\Common\Model
	{
		$address = $this->request->get('address');

		Assert::notNull($address, 'address');
		Assert::isArray($address, 'address');

		return TradingAction\Request\Address::initialize($address);
	}

	protected function getRequestMode() : \YandexPay\Pay\Reference\Common\Model
	{
		$mode = $this->request->get('mode');

		Assert::notNull($mode, 'mode');
		Assert::isString($mode, 'mode');

		return TradingAction\Request\Mode::initialize(['mode' => $mode]);
	}

	protected function getRequestOrder() : \YandexPay\Pay\Reference\Common\Model
	{
		$order = $this->request->get('order');

		Assert::notNull($order, 'order');
		Assert::isArray($order, 'order');

		return TradingAction\Request\Order::initialize($order);
	}

	protected function getRequestDelivery() : \YandexPay\Pay\Reference\Common\Model
	{
		$delivery = $this->request->get('delivery');

		Assert::notNull($delivery, 'delivery');
		Assert::isArray($delivery, 'delivery');

		return TradingAction\Request\Delivery::initialize($delivery);
	}

	protected function getRequestPayment() : \YandexPay\Pay\Reference\Common\Model
	{
		$payment = $this->request->get('payment');

		Assert::notNull($payment, 'payment');
		Assert::isArray($payment, 'payment');

		return TradingAction\Request\Payment::initialize($payment);
	}

	protected function getCalculationDeliveries(EntityReference\Order $order) : array
	{
		$result = [];
		$deliveryService = $this->environment->getDelivery();
		$compatibleIds = $deliveryService->getRestricted($order);

		if (empty($compatibleIds))
		{
			return $result;
		}

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

	/**
	 * @param EntityReference\Order $order
	 * @param string $targetType
	 *
	 * @return array
	 */
	protected function calculateDeliveries(EntityReference\Order $order, string $targetType) : array
	{
		$result = [];

		$deliveryService = $this->environment->getDelivery();
		$compatibleIds = $this->getCalculationDeliveries($order);

		foreach ($compatibleIds as $deliveryId)
		{
			$type = $deliveryService->suggestDeliveryType($deliveryId);

			if ($type !== $targetType) { continue; }

			if (!$deliveryService->isCompatible($deliveryId, $order)) { continue; }

			$calculationResult = $deliveryService->calculate($deliveryId, $order);

			if (!$calculationResult->isSuccess()) { continue; }

			$result[] = [
				'id'        => (string)$calculationResult->getDeliveryId(),
				'label'     => $calculationResult->getServiceName(),
				'amount'    => (string)$calculationResult->getPrice(),
				'provider'  => 'custom', //todo
				'category'  => $calculationResult->getCategory(), //todo
				'date'      => $calculationResult->getDateFrom()->getTimestamp(), //todo
				'stores'    => $calculationResult->getStores(),
			];
		}

		return $result;
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
		$this->setup->fillPersonTypeId();
		$this->setup->fillSiteId();

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

	protected function getProductsAction() : void
	{
		$order = $this->getOrder();
		$this->fillBasket($order);

		$basketItems = $order->getBasketItemsData();

		Exceptions\Facade::handleResult($basketItems);

		echo Main\Web\Json::encode($basketItems->getData());
	}
}