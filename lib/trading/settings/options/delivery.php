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
				'MODAL_WIDTH' => 600,
				'MODAL_HEIGHT' => 450,
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
				'VALUES' => [
					[
						'ID' => 1,
						'VALUE' => 'Pickup',
					],
					[
						'ID' => 2,
						'VALUE' => 'Delivery',
					],
				], // todo $this->getDeliveryEnum($environment, $siteId),
			],
			'TYPE' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('TYPE'),
				'HELP' => self::getMessage('TYPE_HELP'),
				'VALUES' => [
					[
						'ID' => 'PICKUP',
						'VALUE' => 'PICKUP',
					],
					[
						'ID' => 'DELIVERY',
						'VALUE' => 'DELIVERY',
					],
				], // todo
			],
		];
	}
}