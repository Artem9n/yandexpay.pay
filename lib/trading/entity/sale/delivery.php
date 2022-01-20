<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Catalog;
use Bitrix\Sale\Delivery\Restrictions\BySite;
use YandexPay\Pay;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class Delivery extends EntityReference\Delivery
{
	use Concerns\HasMessage;

	/** @var Environment */
	protected $environment;
	/** @var Sale\Delivery\Services\Base[] */
	protected $deliveryServices = [];
	/** @var array<string, bool> */
	protected $existsDeliveryDiscount = [];

	public const EMPTY_DELIVERY = 'emptyDelivery';

	public const CATEGORY_STANDART = 'standart';
	public const CATEGORY_EXPRESS = 'express';
	public const CATEGORY_TODAY = 'today';

	public const DELIVERY_TYPE = 'delivery';
	public const PICKUP_TYPE = 'pickup';

	protected static function includeMessages()
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function isRequired() : bool
	{
		$saleVersion = Main\ModuleManager::getVersion('sale');

		return !CheckVersion($saleVersion, '17.0.0');
	}

	public function getEnum($siteId = null) : array
	{
		$deliveries = $this->loadActiveList();

		return $this->filterBySite($deliveries, $siteId);
	}

	protected function loadActiveList() : array
	{
		$listByParent = [];

		foreach (Sale\Delivery\Services\Manager::getActiveList(true) as $id => $fields)
		{
			if ($delivery = Sale\Delivery\Services\Manager::createObject($fields))
			{
				$name = $delivery->getName();
				$parent = $delivery->getParentService();
				$parentId = $parent ? $parent->getId() : 0;

				if (!isset($listByParent[$parentId]))
				{
					$listByParent[$parentId] = [];
				}

				$listByParent[$parentId][] = [
					'ID' => $id,
					'VALUE' => sprintf('[%s] %s', $id, $name),
					'TYPE' => $this->getDeliveryServiceType($delivery),
					'GROUP' => $parent ? $parent->getName() : null,
				];
			}
		}

		return !empty($listByParent) ? array_merge(...$listByParent) : [];
	}

	protected function getDeliveryServiceType(Sale\Delivery\Services\Base $deliveryService) : ?string
	{
		if ($deliveryService->getId() === $this->getEmptyDeliveryId())
		{
			$result = self::EMPTY_DELIVERY;
		}
		else
		{
			$result = null;
		}

		return $result;
	}

	protected function filterBySite($deliveryServices, $siteId) : array
	{
		$result = [];

		if ($siteId === null)
		{
			$result = $deliveryServices;
		}
		else if (!empty($deliveryServices))
		{
			$deliveryIds = array_column($deliveryServices, 'ID');

			if (count($deliveryIds) === 1) // if only one then result boolean
			{
				$deliveryIds[] = -1;
			}

			$validServices = Sale\Delivery\Services\Manager::checkServiceRestriction(
				$deliveryIds,
				$siteId, BySite::class
			);

			if (is_array($validServices))
			{
				$validServicesMap = array_flip($validServices);
			}
			else // is older version
			{
				$validServicesMap = [];

				foreach ($deliveryServices as $delivery)
				{
					$isValid = Sale\Delivery\Services\Manager::checkServiceRestriction(
						$delivery['ID'],
						$siteId, BySite::class
					);

					if ($isValid)
					{
						$validServicesMap[$delivery['ID']] = true;
					}
				}
			}

			foreach ($deliveryServices as $deliveryService)
			{
				if (isset($validServicesMap[$deliveryService['ID']]))
				{
					$result[] = $deliveryService;
				}
			}
		}

		return $result;
	}

	public function getEmptyDeliveryId() : ?int
	{
		return Sale\Delivery\Services\Manager::getEmptyDeliveryServiceId();
	}

	public function getPickupStores(int $deliveryId, EntityReference\Order $order) : array
	{
		try
		{
			$calculatableOrder = $this->getOrderCalculatable($order);
			$service = $this->getDeliveryService($deliveryId);
			$pickup = Pickup\Factory::make($service);

			$result = $pickup->getStores($calculatableOrder);
		}
		catch (Main\SystemException $exception)
		{
			$result = [];
		}

		return $result;
	}

	public function getRestricted(EntityReference\Order $order) : array
	{
		$result = [];
		$calculatableOrder = $this->getOrderCalculatable($order);
		$shipment = $this->getCalculatableShipment($calculatableOrder);
		$services = Sale\Delivery\Services\Manager::getRestrictedList(
			$shipment,
			Sale\Delivery\Restrictions\Manager::MODE_CLIENT
		);

		foreach ($services as $serviceParameters)
		{
			try
			{
				/** @var Sale\Delivery\Services\Base $serviceClassName */
				$serviceClassName = $serviceParameters['CLASS_NAME'];
				$serviceId = (int)$serviceParameters['ID'];

				if (
					$serviceId <= 0
					|| !class_exists($serviceClassName)
					|| $serviceClassName::canHasProfiles()
					|| (
						is_callable($serviceClassName . '::canHasChildren')
						&& $serviceClassName::canHasChildren()
					)
				)
				{
					continue;
				}

				$service = $this->getDeliveryService($serviceId);

				if (
					!$service
					|| $this->getDeliveryServiceType($service) === self::EMPTY_DELIVERY
				)
				{
					continue;
				}

				if (
					!$this->isOrderLocationFilled($calculatableOrder)
					&& $this->hasDeliveryLocationRestriction($serviceId)
				)
				{
					continue;
				}

				$result[] = $serviceId;
			}
			catch (Main\SystemException $exception)
			{
				// silence
			}
		}

		return $result;
	}

	protected function isOrderLocationFilled(Sale\OrderBase $order) : bool
	{
		$propertyCollection = $order->getPropertyCollection();

		if ($propertyCollection === null) { return false; }

		$locationProperty = $propertyCollection->getDeliveryLocation();

		if ($locationProperty === null) { return false; }

		return (string)$locationProperty->getValue() !== '';
	}

	protected function hasDeliveryLocationRestriction($serviceId) : bool
	{
		$result = false;
		$restrictions = Sale\Delivery\Restrictions\Manager::getRestrictionsList($serviceId);

		foreach ($restrictions as $restriction)
		{
			if ($restriction['CLASS_NAME'] === '\Bitrix\Sale\Delivery\Restrictions\ByLocation')
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	public function isCompatible($deliveryId, EntityReference\Order $order) : bool
	{
		try
		{
			$calculatableOrder = $this->getOrderCalculatable($order);
			$shipment = $this->getCalculatableShipment($calculatableOrder);
			$deliveryService = $this->getDeliveryService($deliveryId);

			$result = $deliveryService->isCompatible($shipment);
		}
		catch (Main\SystemException $exception)
		{
			$result = false;
		}

		return $result;
	}

	public function calculate($deliveryId, EntityReference\Order $order) : EntityReference\Delivery\CalculationResult
	{
		$result = new EntityReference\Delivery\CalculationResult();

		try
		{
			$calculatableOrder = $this->getOrderCalculatable($order);
			$shipment = $this->getCalculatableShipment($calculatableOrder);
			$deliveryService = $this->getDeliveryService($deliveryId);
			$currency = $shipment->getCurrency();

			$calculatableOrder->isStartField();

			$shipment->setDeliveryService($deliveryService);

			if ($currency !== $deliveryService->getCurrency())
			{
				$deliveryService->getExtraServices()->setOperationCurrency($currency);
			}

			$calculationResult = $shipment->calculateDelivery();

			if ($calculationResult->isSuccess())
			{
				$shipment->setField('BASE_PRICE_DELIVERY', $calculationResult->getPrice());
			}

			if ($this->hasDeliveryDiscount($calculatableOrder))
			{
				$calculatableOrder->doFinalAction(true);
			}

			Delivery\CalculationFacade::mergeCalculationResult($result, $calculationResult);
			Delivery\CalculationFacade::mergeDeliveryService($result, $deliveryService);
			Delivery\CalculationFacade::mergeOrderData($result, $calculatableOrder);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Pay\Error\Base(
				$exception->getMessage(),
				$exception->getCode()
			));
		}

		return $result;
	}

	public function configureShipment(Order $order, $deliveryId)
	{
		$calculatableOrder = $this->getOrderCalculatable($order);
		$shipment = $this->getCalculatableShipment($calculatableOrder);
		$deliveryService = $this->getDeliveryService($deliveryId);

		$shipment->setDeliveryService($deliveryService);
	}

	protected function getDeliveryService($deliveryId) : Sale\Delivery\Services\Base
	{
		if (!isset($this->deliveryServices[$deliveryId]))
		{
			$this->deliveryServices[$deliveryId] = $this->loadDeliveryService($deliveryId);
		}

		return $this->deliveryServices[$deliveryId];
	}

	protected function loadDeliveryService($deliveryId) : Sale\Delivery\Services\Base
	{
		$deliveryService = Sale\Delivery\Services\Manager::getObjectById($deliveryId);

		if ($deliveryService === null)
		{
			$message = self::getMessage('DELIVERY_SERVICE_NOT_FOUND', [ '#ID#' => $deliveryId ]);
			throw new Main\SystemException($message);
		}

		return $deliveryService;
	}

	/**
	 * @param \YandexPay\Pay\Trading\Entity\Reference\Order $order
	 *
	 * @return \Bitrix\Sale\Order
	 */
	protected function getOrderCalculatable(EntityReference\Order $order) : Sale\OrderBase
	{
		return $order->getCalculatable();
	}

	protected function getCalculatableShipment(Sale\OrderBase $order) : Sale\Shipment
	{
		$result = null;

		/** @var Sale\Shipment $shipment */
		foreach ($order->getShipmentCollection() as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$result = $shipment;
				break;
			}
		}

		if ($result === null)
		{
			$message = self::getMessage('CALCULATED_SHIPMENT_NOT_FOUND');
			throw new Main\SystemException($message);
		}

		return $result;
	}

	protected function hasDeliveryDiscount(Sale\Order $order)
	{
		$siteId = $order->getSiteId();
		$userGroups = Pay\Data\UserGroup::getUserGroups($order->getUserId());
		$cacheKey = $siteId . '|' . implode('.', $userGroups);

		if (!isset($this->existsDeliveryDiscount[$cacheKey]))
		{
			$this->existsDeliveryDiscount[$cacheKey] = $this->searchDeliveryDiscount($siteId, $userGroups);
		}

		return $this->existsDeliveryDiscount[$cacheKey];
	}

	protected function searchDeliveryDiscount($siteId, $userGroups)
	{
		if (!method_exists('CSaleActionCtrlDelivery', 'GetControlID')) { return false; }

		// query discounts

		$queryDiscounts = Sale\Internals\DiscountTable::getList([
			'filter' => [
				'=LID' => $siteId,
				'=ACTIVE' => 'Y',
				[
					'LOGIC' => 'OR',
					'ACTIVE_FROM' => null,
					'>=ACTIVE_FROM' => new Main\Type\DateTime(),
				],
				[
					'LOGIC' => 'OR',
					'ACTIVE_TO' => null,
					'<=ACTIVE_TO' => new Main\Type\DateTime(),
				],
				[
					'LOGIC' => 'OR',
					'%ACTIONS' => serialize(\CSaleActionCtrlDelivery::GetControlID()),
					'%APPLICATION' => '::applyToDelivery(',
				],
			],
			'select' => [ 'ID' ],
		]);

		$discounts = $queryDiscounts->fetchAll();

		if (empty($discounts)) { return false; }

		// test user group access

		$queryAccess = Sale\Internals\DiscountGroupTable::getList(array(
			'select' => ['DISCOUNT_ID'],
			'filter' => [
				'=DISCOUNT_ID' => array_column($discounts, 'ID'),
				'=GROUP_ID' => $userGroups,
				'=ACTIVE' => 'Y',
			],
			'limit' => 1,
		));

		return (bool)$queryAccess->fetch();
	}

	public function suggestDeliveryType($deliveryId, array $supportedTypes = null) : ?string
	{
		$implementedTypes = $this->getSuggestImplementedDeliveryTypes();
		$processTypes = ($supportedTypes === null)
			? $implementedTypes
			: array_intersect($supportedTypes, $implementedTypes);

		if (empty($processTypes)) { return null; }

		$deliveryService = $this->getDeliveryService($deliveryId);
		$result = static::DELIVERY_TYPE;

		foreach ($processTypes as $type)
		{
			if ($this->matchDeliveryType($deliveryService, $type))
			{
				$result = $type;
				break;
			}
		}

		return $result;
	}

	protected function matchDeliveryType(Sale\Delivery\Services\Base $deliveryService, $type) : bool
	{
		$methodName = 'matchDeliveryType' . ucfirst($type);
		$result = false;

		if (method_exists($this, $methodName))
		{
			$result = (bool)$this->{$methodName}($deliveryService);
		}

		return $result;
	}

	protected function matchDeliveryTypePickup(Sale\Delivery\Services\Base $deliveryService) : bool
	{
		try
		{
			Pickup\Factory::make($deliveryService);

			$result = true;
		}
		catch (Main\ArgumentException $exception)
		{
			$result = false;
		}

		return $result;
	}

	protected function getSuggestImplementedDeliveryTypes() : array
	{
		return [
			static::PICKUP_TYPE
		];
	}

	public function testAdminPickupCoords(string $siteId) : Main\Result
	{
		$result = new Main\Result();

		$query = Catalog\StoreTable::getList([
			'filter' => [
				'ACTIVE' => 'Y',
				'SITE_ID' => $siteId,
				[
					'LOGIC' => 'OR',
					[
						'GPS_N' => false,
					],
					[
						'GPS_S' => false,
					],
					[
						'ADDRESS' => false,
					]

				],
			],
			'select' => ['ID']
		]);

		if ($row = $query->fetch())
		{
			$result->addError(new Main\Error(static::getMessage('TEST_PICKUP_COORDS')));
		}

		return $result;
	}
}