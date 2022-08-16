<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings;

class Warehouse extends Settings\Reference\Fieldset
{
	use Concerns\HasMessage;

	public function getCountry() : ?string
	{
		return $this->getValue('COUNTRY')  ?: null;
	}

	public function getLocality() : ?string
	{
		return $this->getValue('LOCALITY')  ?: null;
	}

	public function getStreet() : ?string
	{
		return $this->getValue('STREET') ?: null;
	}

	public function getBuilding() : ?string
	{
		return $this->getValue('BUILDING') ?: null;
	}

	public function getEntrance() : ?string
	{
		return $this->getValue('ENTRANCE') ?: null;
	}

	public function getFloor() : ?string
	{
		return $this->getValue('FLOOR') ?: null;
	}

	public function getLon() : ?float
	{
		return $this->getValue('LOCATION_LON') ?: null;
	}

	public function getLat() : ?float
	{
		return $this->getValue('LOCATION_LAT') ?: null;
	}

	public function getRequiredFields() : array
	{
		return [
			'COUNTRY' => $this->getCountry(),
			'LOCALITY' => $this->getLocality(),
			'BUILDING' => $this->getBuilding(),
			'LOCATION_LON' => $this->getLon(),
			'LOCATION_LAT' => $this->getLat(),
		];
	}

	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return parent::getFieldDescription($environment, $siteId) + [
			'SETTINGS' => [
				'SUMMARY' => '#COUNTRY#, #LOCALITY#, #STREET# (#BUILDING#)',
				'LAYOUT' => 'summary',
				'MODAL_WIDTH' => 800,
				'MODAL_HEIGHT' => 600,
			],
		];
	}
	
	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'COUNTRY' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('COUNTRY'),
			],
			'LOCALITY' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('LOCALITY'),
			],
			'STREET' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('STREET'),
			],
			'BUILDING' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('BUILDING'),
			],
			'ENTRANCE' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('ENTRANCE'),
			],
			'FLOOR' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('FLOOR'),
			],
			'LOCATION_LAT' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('LOCATION_LAT'),
			],
			'LOCATION_LON' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('LOCATION_LON'),
			],
		];
	}
}