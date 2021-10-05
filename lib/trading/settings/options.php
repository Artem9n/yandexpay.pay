<?php

namespace YandexPay\Pay\Trading\Settings;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Utils\Userfield\DependField;

class Options extends Reference\Skeleton
{
	use Concerns\HasMessage;

	public function strictDeliveryOptions() : bool
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
			(int)$this->getValue('PROPERTY_PHONE') <= 0
			&& (int)$this->getValue('PROPERTY_EMAIL') <= 0
		)
		{
			//$result->addError(new Main\Error(self::getMessage('VALIDATE_ONE_OF_EMAIL_PHONE')));
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
			+ $this->getDeliveryFields($environment, $siteId)
			+ $this->getPickupFields($environment, $siteId)
			+ $this->getBuyerProperties($environment, $siteId);
	}

	protected function getHandlerFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			// todo
		];
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
			'PROPERTY_FIRST_NAME' => [
				'TYPE' => 'orderProperty',
				'NAME' => self::getMessage('PROPERTY_FIRST_NAME'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				/*'DEPEND' => [
					'SPLIT_BUYER_NAME' => [
						'RULE' => DependField::RULE_EMPTY,
						'VALUE' => false,
					],
				],*/
				'SETTINGS' => [
					'TYPE' => 'FIRST_NAME',
				],
			],
			'PROPERTY_LAST_NAME' => [
				'TYPE' => 'orderProperty',
				'NAME' => self::getMessage('PROPERTY_LAST_NAME'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				/*'DEPEND' => [
					'SPLIT_BUYER_NAME' => [
						'RULE' => DependField::RULE_EMPTY,
						'VALUE' => false,
					],
				],*/
				'SETTINGS' => [
					'TYPE' => 'LAST_NAME',
					'CAPTION_NO_VALUE' => 'NO_',
				],
			],
			'PROPERTY_MIDDLE_NAME' => [
				'TYPE' => 'orderProperty',
				'NAME' => self::getMessage('PROPERTY_MIDDLE_NAME'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				/*'DEPEND' => [
					'SPLIT_BUYER_NAME' => [
						'RULE' => DependField::RULE_EMPTY,
						'VALUE' => false,
					],
				],*/
				'SETTINGS' => [
					'TYPE' => 'MIDDLE_NAME',
				],
			],
			'PROPERTY_EMAIL' => [
				'TYPE' => 'orderProperty',
				'NAME' => self::getMessage('PROPERTY_EMAIL'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'EMAIL',
				],
			],
			'PROPERTY_PHONE' => [
				'TYPE' => 'orderProperty',
				'NAME' => self::getMessage('PROPERTY_PHONE'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'PHONE',
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
}