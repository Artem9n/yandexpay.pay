<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action as TradingAction;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;
use YandexPay\Pay\Utils\JsonBodyFilter;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); };

Loc::loadMessages(__FILE__);

class TradingCart extends \CBitrixComponent
{
	/** @var EntityReference\Environment */
	protected $environment;

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

		//$this->fillAddress($order);

		$calculatedDeliveries = $this->calculateDeliveries($order, 'DELIVERY');
	}

	protected function pickupOptionsAction() : void
	{
		$order = $this->getOrder();

		$this->fillAddress($order);

		$calculatedDeliveries = $this->calculateDeliveries($order, 'PICKUP');


	}

	protected function couponAction() : float
	{
		$order = $this->getOrder();

		$this->fillLocation($order);
		$this->fillAddress($order);
		$this->fillCoupon($order);

		$order->doFinalAction(true);

		return $order->getPrice();
	}

	protected function orderAcceptAction()
	{
		$order = $this->getOrder();

		$this->fillLocation($order);
		//$this->fillAddress($order);
		//$this->fillDelivery();
	}

	protected function getOrder() : EntityReference\Order
	{
		return $this->environment->getOrderRegistry()->createOrder(
			$this->request->get('siteId'),
			$this->request->get('userId'),
			$this->request->get('currency')
		);
	}

	protected function fillLocation(EntityReference\Order $order) : void
	{
		$address = $this->getRequestAddress();
		$locationService = $this->environment->getLocation();
		$locationId = $locationService->getLocation($address->getFields());

		$orderResult = $order->setLocation($locationId);

		if (!$orderResult->isSuccess())
		{
			throw new Main\SystemException($orderResult->getErrorMessages());
		}

		die;
	}

	protected function fillAddress(EntityReference\Order $order) : void
	{
		$address = $this->getRequestAddress();
		$addressString = $address->getMeaningfulAddress();

		$order->setAddress($addressString);
		$order->setProperties([
			'ID' => $address->getRegion(),
		]);
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
	 * @return Calcul[]
	 */
	protected function calculateDeliveries(EntityReference\Order $order, $targetType) : array
	{
		$result = [];
		$deliveryService = $this->environment->getDelivery();

		echo '<pre>';
		print_r($deliveryService->getRestricted($order));
		echo '</pre>';
		die;

		foreach ($deliveryService->getRestricted($order) as $deliveryId)
		{
			/*$type = $deliveryService->getType($deliveryId);

			if ($type !== $targetType) { continue; }

			if (!$deliveryService->isCompatible($order)) { continue; }

			$calculationResult = $deliveryService->calculateConcrete();

			if (!$calculationResult->isSuccess()) { continue; }

			$result[] = $calculationResult;*/
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
	}

	protected function getLang(string $code, $replace = null, $language = null): string
	{
		return Main\Localization\Loc::getMessage('YANDEX_PAY_TRADING_CART_' . $code, $replace, $language);
	}


}