<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings\Reference\Fieldset;
use YandexPay\Pay\Utils;

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

	public function getUserId() : ?string
	{
		return $this->getValue('EMERGENCY_CONTACT') ?: null;
	}

	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$defaultsValue = $this->makeDeliveryOptionsDefaults($environment);

		return parent::getFieldDescription($environment, $siteId) + [
			'SETTINGS' => [
				'SUMMARY' => '#TYPE# &laquo;#ID#&raquo;',
				'LAYOUT' => 'summary',
				'MODAL_WIDTH' => 450,
				'MODAL_HEIGHT' => 300,
				'DEFAULT_VALUE' => $defaultsValue,
			],
		];
	}

	protected function makeDeliveryOptionsDefaults(Entity\Reference\Environment $environment) : array
	{
		$result = [];

		$yandexDelivery = $environment->getDelivery()->getYandexDeliveryService();

		if ($yandexDelivery === null) { return $result; }

		$result[] = [
			'ID' => $yandexDelivery->getId(),
			'TYPE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
		];

		return $result;
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
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
				'HELP' => self::getMessage('TYPE_HELP'),
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
			'WAREHOUSE' => $this->getWarehouse()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'warehouse',
				'NAME' => self::getMessage('WAREHOUSE'),
				'GROUP' => self::getMessage('GROUP_SETTINGS'),
				'DEPEND' => [
					'TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
					],
				],
			],
			'EMERGENCY_CONTACT' => [
				'TYPE' => 'user',
				'NAME' => self::getMessage('EMERGENCY_CONTACT'),
				'GROUP' => self::getMessage('GROUP_SETTINGS'),
				'DEPEND' => [
					'TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE,
					],
				],
			],
		];
	}

	protected function validateFieldset() : Main\Result
	{
		$result = new Main\Result();

		if ($this->getType() !==  Entity\Sale\Delivery::YANDEX_DELIVERY_TYPE) { return $result; }

		$errors = [];

		$warehouse = $this->getWarehouse();
		$requiredFields = $warehouse->getRequiredFields();

		foreach ($requiredFields as $code => $value)
		{
			if ($value !== null) { continue; }

			$errors[] = static::getMessage(sprintf('WAREHOUSE_FIELD_%s_REQUIRED', $code));
		}

		if ($this->getUserId() === null)
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