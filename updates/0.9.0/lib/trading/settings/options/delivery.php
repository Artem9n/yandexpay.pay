<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings\Reference\Fieldset;

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

	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return parent::getFieldDescription($environment, $siteId) + [
			'SETTINGS' => [
				'SUMMARY' => '#TYPE# &laquo;#ID#&raquo;',
				'LAYOUT' => 'summary',
				'MODAL_WIDTH' => 450,
				'MODAL_HEIGHT' => 300,
			],
		];
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'ID' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('ID'),
				'VALUES' => $environment->getDelivery()->getEnum($siteId)
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
				],
			],
		];
	}
}