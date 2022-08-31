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

	public function getWarehouse() : Warehouse
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->getFieldset('WAREHOUSE');
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

	public function getEmergencyContact() : ?int
	{
		return $this->getValue('EMERGENCY_CONTACT') ?: null;
	}

	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$defaultsValue = $this->makeDeliveryOptionsDefaults($environment, $siteId);

		return parent::getFieldDescription($environment, $siteId) + [
			'SETTINGS' => [
				'SUMMARY' => '#TYPE# &laquo;#ID#&raquo;',
				'LAYOUT' => 'summary',
				'MODAL_WIDTH' => 550,
				'MODAL_HEIGHT' => 300,
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

		$warehouseHelp = self::getMessage('STORE_WAREHOUSE_HELP', [
			'#LINK#' => $this->getHelpLinkUserField(Ui\UserField\WarehouseType::USER_TYPE_ID)
		]);
		$contactHelp = self::getMessage('STORE_CONTACT_HELP', [
			'#LINK#' => $this->getHelpLinkUserField(Ui\UserField\UserType::USER_TYPE_ID)
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
				],
			],
		];
	}

	protected function validateFieldset() : Main\Result
	{
		$result = new Main\Result();

		if (
			$this->getType() !==  Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE
			|| !empty($this->getCatalogStore())
		) { return $result; }

		$errors = [];

		$warehouse = $this->getWarehouse()->validate();

		if (!$warehouse->isSuccess())
		{
			$result->addErrors($warehouse->getErrors());
		}

		if ($this->getEmergencyContact() === null)
		{
			$errors[] = static::getMessage('FIELD_EMERGENCY_REQUIRED');
		}

		if (!empty($errors))
		{
			$result->addError(new Main\Error(implode(', ', $errors)));
		}

		return $result;
	}

	protected function getFieldsetMap() : array
	{
		return [
			'WAREHOUSE' => Warehouse::class,
		];
	}
}