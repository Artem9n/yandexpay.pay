<?php

namespace YandexPay\Pay\Trading\Settings;

use Bitrix\Main;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Utils;

class Options extends Reference\Skeleton
{
	use Concerns\HasMessage;

	public function isDeliveryStrict() : bool
	{
		return $this->getValue('DELIVERY_STRICT', false);
	}
	
	public function getDeliveryOptions() : Options\DeliveryCollection
	{
		return $this->getFieldsetCollection('DELIVERY_OPTIONS');
	}

	public function getPersonTypeId() : int
	{
		return (int)$this->requireValue('PERSON_TYPE_ID');
	}

	protected function validateSelf() : Main\Result
	{
		$result = new Main\Result();

		if (
			(int)$this->getValue('USE_BUYER_PHONE') <= 0
			&& (int)$this->getValue('USE_BUYER_EMAIL') <= 0
		)
		{
			$result->addError(new Main\Error(self::getMessage('VALIDATE_ONE_OF_EMAIL_PHONE')));
		}

		return $result;
	}

	public function getTabs() : array
	{
		return [
			'COMMON' => [
				'name' => self::getMessage('TAB_COMMON'),
				'sort' => 1000,
			],
		];
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		/** @noinspection AdditionOperationOnArraysInspection */
		return
			$this->getHandlerFields($environment, $siteId)
			+ $this->getPurchaseFields($environment, $siteId)
			+ $this->getDeliveryFields($environment, $siteId)
			+ $this->getPickupFields($environment, $siteId)
			+ $this->getBuyerProperties($environment, $siteId)
			+ $this->getAddressCommonFields($environment, $siteId);
	}

	protected function getHandlerFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			// todo
		];
	}

	protected function getPurchaseFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'PURCHASE_URL' => [
				'TYPE' => 'string',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('PURCHASE_URL'),
				'GROUP' => self::getMessage('COMMON'),
				'SORT' => 2000,
				'VALUE' => static::getPurchaseUrl(),
				'SETTINGS' => [
					'READONLY' => true,
				]
			]
		];
	}

	public static function getPurchaseUrl() : string
	{
		return Utils\Url::absolutizePath(BX_ROOT . '/tools/' . Config::getModuleName() . '/purchase.php');
	}

	protected function getDeliveryFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$deliveryOptions = $this->getDeliveryOptions();
		
		return [
			'DELIVERY_STRICT' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('DELIVERY_GROUP'),
				'NAME' => self::getMessage('DELIVERY_STRICT'),
				'SORT' => 1000,
			],
			'DELIVERY_OPTIONS' => $deliveryOptions->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'GROUP' => self::getMessage('DELIVERY_GROUP'),
				'NAME' => self::getMessage('DELIVERY_OPTIONS'),
				'NOTE' => self::getMessage('DELIVERY_OPTIONS_NOTE'),
				'SORT' => 1010,
			],
		];
	}

	protected function getPickupFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'PICKUP_PAYSYSTEM' => [
				'TYPE' => 'enumeration',
				'GROUP' => self::getMessage('PICKUP'),
				'NAME' => self::getMessage('PICKUP_PAYSYSTEM'),
				'SORT' => 2010,
				'VALUES' => $environment->getPaySystem()->getEnum($siteId),
			],
		];
	}

	protected function getBuyerProperties(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$propertyEnum = $environment->getProperty()->getEnum($this->getPersonTypeId());

		return [
			'ALLOW_ENTER_COMMENT' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('YANDEX'),
				'NAME' => self::getMessage('ALLOW_ENTER_COMMENT'),
				'SORT' => 3000,
			],
			'ALLOW_ENTER_COUPON' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('YANDEX'),
				'NAME' => self::getMessage('ALLOW_ENTER_COUPON'),
				'SORT' => 3005,
			],
			'USE_BUYER_NAME' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('YANDEX'),
				'NAME' => self::getMessage('USE_BUYER_NAME'),
				'SORT' => 3005,
			],
			'USE_BUYER_EMAIL' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('YANDEX'),
				'NAME' => self::getMessage('USE_BUYER_EMAIL'),
				'SORT' => 3005
			],
			'USE_BUYER_PHONE' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('YANDEX'),
				'NAME' => self::getMessage('USE_BUYER_PHONE'),
				'SORT' => 3007
			],
			'PROPERTY_LAST_NAME' => [
				'TYPE' => 'orderProperty',
				'GROUP' => self::getMessage('BUYER'),
				'NAME' => self::getMessage('PROPERTY_LAST_NAME'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'LAST_NAME',
					'CAPTION_NO_VALUE' => self::getMessage('NO_VALUE'),
				],
			],
			'PROPERTY_FIRST_NAME' => [
				'TYPE' => 'orderProperty',
				'GROUP' => self::getMessage('BUYER'),
				'NAME' => self::getMessage('PROPERTY_FIRST_NAME'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'FIRST_NAME',
					'CAPTION_NO_VALUE' => self::getMessage('NO_VALUE'),
				],
			],
			'PROPERTY_MIDDLE_NAME' => [
				'TYPE' => 'orderProperty',
				'GROUP' => self::getMessage('BUYER'),
				'NAME' => self::getMessage('PROPERTY_MIDDLE_NAME'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'MIDDLE_NAME',
					'CAPTION_NO_VALUE' => self::getMessage('NO_VALUE'),
				],
			],
			'PROPERTY_EMAIL' => [
				'TYPE' => 'orderProperty',
				'GROUP' => self::getMessage('BUYER'),
				'NAME' => self::getMessage('PROPERTY_EMAIL'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'EMAIL',
					'CAPTION_NO_VALUE' => self::getMessage('NO_VALUE'),
				],
			],
			'PROPERTY_PHONE' => [
				'TYPE' => 'orderProperty',
				'GROUP' => self::getMessage('BUYER'),
				'NAME' => self::getMessage('PROPERTY_PHONE'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'PHONE',
					'CAPTION_NO_VALUE' => self::getMessage('NO_VALUE'),
				],
			],
		];
	}

	protected function getFieldsetCollectionMap() : array
	{
		return [
			'DELIVERY_OPTIONS' => Options\DeliveryCollection::class,
		];
	}

	protected function getAddressCommonFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$propertyEnum = $environment->getProperty()->getEnum($this->getPersonTypeId());

		$propertyFields = [];
		$keys = [
			'ZIP',
			'CITY',
			'ADDRESS',
		];

		foreach ($keys as $key)
		{
			$propertyFields['PROPERTY_' . $key] = [
				'NAME' => static::getMessage('ADDRESS_' . $key, null, $key),
				'TYPE' => 'orderProperty',
				'GROUP' => static::getMessage('ADDRESS_GROUP'),
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => $key,
					'CAPTION_NO_VALUE' => self::getMessage('NO_VALUE'),
				],
			];
		}

		return  $propertyFields;
	}

	public function getProperty(string $fieldName)
	{
		return $this->getValue('PROPERTY_' . $fieldName);
	}
}