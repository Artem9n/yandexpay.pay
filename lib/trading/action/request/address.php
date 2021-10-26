<?php
namespace YandexPay\Pay\Trading\Action\Request;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Address extends Model
{
	use Concerns\HasMessage;

	protected static function includeMessages() : void
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public function getMeaningfulAddress(array $skipAdditionalTypes = []) : string
	{
		$values = $this->getAddressValues();

		return $this->combineAddress($values, $skipAdditionalTypes);
	}

	public function getMeaningfulCity() : string
	{
		$values = $this->getCityValues();

		return $this->combineValues($values);
	}

	public function getMeaningfulZip() : string
	{
		$values = $this->getZipValues();

		return $this->combineValues($values);
	}

	public function getZipValues() : array
	{
		return [
			'POSTCODE' => $this->getField('zip'),
		];
	}

	public function getCityValues() : array
	{
		return [
			'COUNTRY' => $this->getField('country'),
			'CITY' => $this->getField('locality'),
		];
	}

	protected function combineValues($values) : string
	{
		$values = array_filter($values, static function($value) { return (string)$value !== ''; });

		return implode(', ', $values);
	}

	public function getAddressValues() : array
	{
		return [
			'BUILDING' => $this->getField('building'),
			'STREET' => $this->getField('street'),
			'ENTRANCE' => $this->getField('entrance'),
			'INTERCOM' => $this->getField('intercom'),
			'FLOOR' => $this->getField('floor'),
			'ROOM' => $this->getField('room')
		];
	}

	protected function combineAddress($values, array $skipAdditionalTypes = []) : string
	{
		$commonFields = [
			'STREET' => true,
			'BUILDING' => true,
			'ROOM' => true
		];
		$commonValues = [];
		$additionalValues = [];

		foreach ($values as $type => $value)
		{
			if ((string)$value === '') { continue; }

			$displayValue = $this->getAddressTypeValue($type, $value);

			if (isset($commonFields[$type]))
			{
				$commonValues[] = $displayValue;
			}
			else if (!in_array($type, $skipAdditionalTypes, true))
			{
				$additionalValues[] = $displayValue;
			}
		}

		$result = implode(', ', $commonValues);

		if (!empty($additionalValues))
		{
			$result .= ' (' . implode(', ', $additionalValues) . ')';
		}

		return $result;
	}

	protected function getAddressTypeValue($type, $value) : string
	{
		$prefix = $this->getAddressTypePrefix($type);

		return
			($prefix !== '' ? $prefix . ' ' : '')
			. $value;
	}

	protected function getAddressTypePrefix($type) : string
	{
		return static::getMessage('TYPE_' . $type, null, '');
	}

	public static function getFieldTitle(string $fieldName) : string
	{
		return static::getMessage('FIELD_' . $fieldName);
	}

	public function getCountry() : string
	{
		$result = $this->getField('country');

		Assert::notNull($result, 'country');
		Assert::isString($result, 'country');

		return $result;
	}

	public function getEntrance() : ?string
	{
		$result = $this->getField('entrance');

		if ($result === null) { return null; }

		Assert::isString($result, 'entrance');

		return $result;
	}

	public function getZip() : string
	{
		$result = $this->getField('zip');

		Assert::notNull($result, 'zip');
		Assert::isString($result, 'zip');

		return $result;
	}

	public function getLocality() : string
	{
		$result = $this->getField('locality');

		Assert::notNull($result, 'locality');
		Assert::isString($result, 'locality');

		return $result;
	}

	public function getStreet() : ?string
	{
		$result = $this->getField('street');

		if ($result === null) { return null; }

		Assert::isString($result, 'street');

		return $result;
	}

	public function getBuilding() : ?string
	{
		$result = $this->getField('building');

		if ($result === null) { return null; }

		Assert::isString($result, 'building');

		return $result;
	}

	public function getFloor() : ?string
	{
		$result = $this->getField('floor');

		if ($result === null) { return null; }

		Assert::isString($result, 'floor');

		return $result;
	}

	public function getRoom() : ?string
	{
		$result = $this->getField('room');

		if ($result === null) { return null; }

		Assert::isString($result, 'room');

		return $result;
	}

	public function getIntercom() : ?string
	{
		$result = $this->getField('intercom');

		if ($result === null) { return null; }

		Assert::isString($result, 'intercom');

		return $result;
	}

	public function getComment() : ?string
	{
		$result = $this->getField('comment');

		if ($result === null) { return null; }

		Assert::isString($result, 'comment');

		return $result;
	}

	public function getCoordinates() : ?Model
	{
		$result = $this->getField('location');

		if ($result === null) { return null; }

		Assert::isArray($result, 'location');

		return Address\Coordinates::initialize($result);
	}
}