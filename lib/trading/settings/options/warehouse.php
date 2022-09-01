<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings;

class Warehouse extends Settings\Reference\Fieldset
{
	use Concerns\HasMessage;

	public function getCountry() : string
	{
		return $this->requireValue('COUNTRY');
	}

	public function getLocality() : string
	{
		return $this->requireValue('LOCALITY');
	}

	public function getStreet() : ?string
	{
		return $this->getValue('STREET') ?: null;
	}

	public function getBuilding() : string
	{
		return $this->requireValue('BUILDING');
	}

	public function getEntrance() : ?string
	{
		return $this->getValue('ENTRANCE') ?: null;
	}

	public function getFloor() : ?string
	{
		return $this->getValue('FLOOR') ?: null;
	}

	public function getLon() : float
	{
		return $this->requireValue('LOCATION_LON');
	}

	public function getLat() : float
	{
		return $this->requireValue('LOCATION_LAT');
	}

	public function getRequiredFields() : array
	{
		return [
			'COUNTRY',
			'LOCALITY',
			'BUILDING',
			'LOCATION_LON',
			'LOCATION_LAT',
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
			'FULL_ADDRESS' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('FULL_ADDRESS'),
			],
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

	public function validateSelf() : Main\Result
	{
		$result = new Main\Result();

		foreach ($this->getRequiredFields() as $code)
		{
			if (!empty($this->getValue($code))) { continue; }
			$message = static::getMessage(sprintf('FIELD_%s_REQUIRED', $code));
			$result->addError(new Main\Error($message));
		}

		return $result;
	}
}