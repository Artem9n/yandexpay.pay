<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings\Reference\Fieldset;
use YandexPay\Pay\Utils;
use YandexPay\Pay\Ui;

class Delivery extends Fieldset
{
	use Concerns\HasMessage;

	public function getServiceId() : int
	{
		return (int)$this->requireValue('ID');
	}

	public function getType() : string
	{
		return $this->requireValue('TYPE');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getWarehouse() : Warehouse
	{
		return $this->getFieldset('WAREHOUSE');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getShipmentSchedule() : ShipmentSchedule
	{
		return $this->getFieldset('SHIPMENT_SCHEDULE');
	}

	public function getCatalogStore() : string
	{
		return (string)$this->getValue('CATALOG_STORE');
	}

	public function getStoreWarehouseField() : string
	{
		return $this->requireValue('STORE_WAREHOUSE');
	}

	public function getStoreContactField() : string
	{
		return $this->requireValue('STORE_CONTACT');
	}

	public function getEmergencyContact() : int
	{
		return $this->requireValue('EMERGENCY_CONTACT');
	}

	public function getStoreShipmentSchedule() : string
	{
		return $this->requireValue('STORE_SHIPMENT_SCHEDULE');
	}

	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$defaultsValue = $this->makeDeliveryOptionsDefaults($environment, $siteId);

		return parent::getFieldDescription($environment, $siteId) + [
			'SETTINGS' => [
				'SUMMARY' => '#TYPE# &laquo;#ID#&raquo;',
				'LAYOUT' => 'summary',
				'MODAL_WIDTH' => 650,
				'MODAL_HEIGHT' => 400,
				'DEFAULT_VALUE' => $defaultsValue,
			],
		];
	}

	protected function makeDeliveryOptionsDefaults(Entity\Reference\Environment $environment, string $siteId) : array
	{
		global $USER;

		$result = [];

		$yandexDelivery = $environment->getDelivery()->getYandexDeliveryService();
		$deliveryId = $environment->getDelivery()->getEmptyDeliveryId();

		if ($yandexDelivery !== null)
		{
			$deliveryId = $yandexDelivery->getId();
		}

		$result[] = [
			'ID' => $deliveryId,
			'TYPE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
			'WAREHOUSE' => $environment->getDelivery()->getDefaultAddress($siteId),
			'EMERGENCY_CONTACT' => $USER->GetID(),
		];

		return $result;
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return
			$this->getCommonFields($environment, $siteId)
			+ $this->getYandexDeliveryFields($environment, $siteId)
			+ $this->getCatalogFields($environment, $siteId);
	}

	protected function getCommonFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'ID' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('ID'),
				'VALUES' => $environment->getDelivery()->getEnum($siteId),
			],
			'TYPE' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('TYPE'),
				'VALUES' => [
					[
						'ID' => Entity\Sale\Delivery::PICKUP_TYPE,
						'VALUE' => self::getMessage('TYPE_PICKUP'),
					],
					[
						'ID' => Entity\Sale\Delivery::DELIVERY_TYPE,
						'VALUE' => self::getMessage('TYPE_DELIVERY'),
					],
					[
						'ID' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
						'VALUE' => self::getMessage('TYPE_YANDEX_DELIVERY'),
					],
				],
			],
		];
	}

	protected function getYandexDeliveryFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'CATALOG_STORE' => [
				'TYPE' => 'enumeration',
				'NAME' => self::getMessage('CATALOG_STORE'),
				'HELP' => self::getMessage('CATALOG_STORE_HELP'),
				'GROUP' => self::getMessage('GROUP_SETTINGS'),
				'VALUES' => $environment->getStore()->expressStrategyEnum(),
				'DEPEND' => [
					'TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
					],
				],
				'SETTINGS' => [
					'ALLOW_NO_VALUE' => 'Y',
					'CAPTION_NO_VALUE' => self::getMessage('CATALOG_STORE_DEFAULT'),
				],
			],
			'WAREHOUSE' => $this->getWarehouse()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'warehouse',
				'NAME' => self::getMessage('WAREHOUSE'),
				'HELP' => self::getMessage('WAREHOUSE_HELP'),
				'DEPEND' => [
					'CATALOG_STORE' => [
						'RULE' => Utils\Userfield\DependField::RULE_EMPTY,
						'VALUE' => true,
					],
					'TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
					],
				],
			],
			'SHIPMENT_SCHEDULE' => $this->getShipmentSchedule()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'shipmentschedule',
				'NAME' => self::getMessage('SHIPMENT_SCHEDULE'),
				'HELP' => self::getMessage('SHIPMENT_SCHEDULE_HELP'),
				'DEPEND' => [
					'CATALOG_STORE' => [
						'RULE' => Utils\Userfield\DependField::RULE_EMPTY,
						'VALUE' => true,
					],
					'TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
					],
				],
			],
			'EMERGENCY_CONTACT' => [
				'TYPE' => 'user',
				'NAME' => self::getMessage('EMERGENCY_CONTACT'),
				'HELP' => self::getMessage('EMERGENCY_CONTACT_HELP'),
				'DEPEND' => [
					'CATALOG_STORE' => [
						'RULE' => Utils\Userfield\DependField::RULE_EMPTY,
						'VALUE' => true,
					],
					'TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
					],
				],
			],
		];
	}

	protected function getHelpLinkUserField(string $typeId) : string
	{
		return sprintf('/bitrix/admin/userfield_edit.php?lang=ru&ENTITY_ID=CAT_STORE&USER_TYPE_ID=%s', $typeId);
	}

	protected function getCatalogFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$warehouseEnum = $environment->getStore()->getFields(Entity\Reference\Store::FIELD_BEHAVIOR_WAREHOUSE);
		$contactEnum = $environment->getStore()->getFields(Entity\Reference\Store::FIELD_BEHAVIOR_CONTACT);
		$scheduleEnum = $environment->getStore()->getFields(Entity\Reference\Store::FIELD_BEHAVIOR_SHIPMENT_SCHEDULE);

		$warehouseHelp = self::getMessage('STORE_WAREHOUSE_HELP', [
			'#LINK#' => $this->getHelpLinkUserField(Ui\UserField\WarehouseType::USER_TYPE_ID)
		]);
		$contactHelp = self::getMessage('STORE_CONTACT_HELP', [
			'#LINK#' => $this->getHelpLinkUserField(Ui\UserField\UserType::USER_TYPE_ID)
		]);
		$scheduleHelp = self::getMessage('STORE_SHIPMENT_SCHEDULE_HELP', [
			'#LINK#' => $this->getHelpLinkUserField(Ui\UserField\ShipmentScheduleType::USER_TYPE_ID)
		]);

		return [
			'STORE_WAREHOUSE' => [
				'TYPE' => 'enumeration',
				'NAME' => self::getMessage('STORE_WAREHOUSE'),
				'HELP' => !empty($warehouseEnum) ? $warehouseHelp : null,
				'NOTE' => empty($warehouseEnum) ? $warehouseHelp : null,
				'VALUES' => $warehouseEnum,
				'DEPEND' => [
					'CATALOG_STORE' => [
						'RULE' => Utils\Userfield\DependField::RULE_EMPTY,
						'VALUE' => false,
					],
					'TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
					],
				],
			],
			'STORE_SHIPMENT_SCHEDULE' => [
				'TYPE' => 'enumeration',
				'NAME' => self::getMessage('STORE_SHIPMENT_SCHEDULE'),
				'HELP' => !empty($scheduleEnum) ? $scheduleHelp : null,
				'NOTE' => empty($scheduleEnum) ? $scheduleHelp : null,
				'VALUES' => $scheduleEnum,
				'DEPEND' => [
					'CATALOG_STORE' => [
						'RULE' => Utils\Userfield\DependField::RULE_EMPTY,
						'VALUE' => false,
					],
					'TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
					],
				],
			],
			'STORE_CONTACT' => [
				'TYPE' => 'enumeration',
				'NAME' => self::getMessage('STORE_CONTACT'),
				'HELP' => !empty($contactEnum) ? $contactHelp : null,
				'NOTE' => empty($contactEnum) ? $contactHelp : null,
				'VALUES' => $contactEnum,
				'DEPEND' => [
					'CATALOG_STORE' => [
						'RULE' => Utils\Userfield\DependField::RULE_EMPTY,
						'VALUE' => false,
					],
					'TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
					],
				],
			],
		];
	}

	protected function validateSelf() : Main\Result
	{
		$result = new Main\Result();

		if (empty($this->getCatalogStore()))
		{
			if (empty($this->getValue('EMERGENCY_CONTACT')))
			{
				$result->addError(new Main\Error(static::getMessage('FIELD_EMERGENCY_REQUIRED')));
			}
		}
		else
		{
			foreach (['STORE_CONTACT', 'STORE_WAREHOUSE', 'STORE_SHIPMENT_SCHEDULE'] as $code)
			{
				if (!empty($this->getValue($code))) { continue; }
				$message = static::getMessage(sprintf('FIELD_%s_REQUIRED', $code));
				$result->addError(new Main\Error($message));
			}
		}

		return $result;
	}

	protected function getFieldsetMap() : array
	{
		return [
			'WAREHOUSE' => Warehouse::class,
			'SHIPMENT_SCHEDULE' => ShipmentSchedule::class,
		];
	}

	protected function validateFieldset() : Main\Result
	{
		$result = new Main\Result();

		if (!empty($this->getCatalogStore())) { return $result; }

		return parent::validateFieldset();
	}

	public function validate() : Main\Result
	{
		$result = new Main\Result();

		if ($this->getType() !==  Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE) { return $result; }

		return parent::validate();
	}
}