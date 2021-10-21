<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action as TradingAction;
use YandexPay\Pay\Trading\Settings as TradingSettings;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;
use YandexPay\Pay\Utils\JsonBodyFilter;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); };

Loc::loadMessages(__FILE__);

class Purchase extends \CBitrixComponent
{
	/** @var EntityReference\Environment */
	protected $environment;
	/** @var TradingSettings\Options */
	protected $options;
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
		$this->fillBasket($order);
		$this->fillLocation($order);


		$this->fillAddress($order);

		$calculatedDeliveries = $this->calculateDeliveries($order, 'DELIVERY');

		echo Main\Web\Json::encode($calculatedDeliveries);
	}

	protected function pickupOptionsAction() : void
	{
		$result = [];

		$order = $this->getOrder();
		$this->fillBasket($order);
		$this->fillLocation($order);

		//$this->fillAddress($order);

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
						'description'   => $store['DESCRIPTION']
					]
				];
			}
		}


		echo '<pre>';
		print_r($result);
		echo '</pre>';
		die;
		//$this->environment->getDelivery()->


	}

	protected function fillBasket(EntityReference\Order $order)
	{
		$addProductResult = $order->addProduct($this->request->get('productId'));

		Exceptions\Facade::handleResult($addProductResult);
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

	protected function orderAcceptAction()
	{
		$userId = $this->createUser();
		$order = $this->getOrder($userId);

		$this->fillStatus($order);
		$this->fillLocation($order);

		pr($userId, 1);
		die;
		//



		//$this->fillLocation($order);
		//$this->fillAddress($order);
		//$this->fillDelivery();




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
			'SITE_ID' => $this->request->get('siteId')
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

		$orderResult = $order->setLocation($locationId);

		Exceptions\Facade::handleResult($orderResult);
	}

	protected function fillAddress(EntityReference\Order $order) : void
	{
		/** @var \YandexPay\Pay\Trading\Action\Request\Address $address */
		$address = $this->getRequestAddress();
		$addressString = $address->getMeaningfulAddress();

		pr($addressString);
		die;

		$order->setAddress($addressString);
		$order->setProperties([
			'ID' => $address->getRegion(),
		]);
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

		foreach ($deliveryService->getRestricted($order) as $deliveryId)
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
				'category'  => 'standart', //todo
				'date'      => 1632517200, //todo
				'stores'    => $calculationResult->getStores()
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
			'sale'
		];
	}

	protected function bootstrap() : void
	{
		$this->environment = EntityRegistry::getEnvironment();
		/*$this->options = */
	}

	protected function getLang(string $code, $replace = null, $language = null): string
	{
		return Main\Localization\Loc::getMessage('YANDEX_PAY_TRADING_CART_' . $code, $replace, $language);
	}
}