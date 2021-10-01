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
		/** @noinspection PhpIncompatibleReturnTypeInspection */
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
		return [
			'USE_BUYER_NAME' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('BUYER'),
				'NAME' => self::getMessage('USE_BUYER_NAME'),
				'SORT' => 3000,
			],
			'PROPERTY_NAME' => [
				'TYPE' => 'enumeration',
				'NAME' => self::getMessage('PROPERTY_NAME'),
				'SORT' => 3010,
				'VALUES' => $environment->getPaySystem()->getEnum($siteId),
				'DEPEND' => [
					'USE_BUYER_NAME' => [
						'RULE' => DependField::RULE_EMPTY,
						'VALUE' => false,
					],
				],
			]
		];
	}

	protected function getFieldsetCollectionMap() : array
	{
		return [
			'DELIVERY_OPTIONS' => Options\DeliveryCollection::class,
		];
	}
}