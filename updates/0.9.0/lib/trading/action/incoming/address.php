<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Incoming;
use YandexPay\Pay\Reference\Concerns;


class Address extends Incoming\Skeleton
{
	use Concerns\HasMessage;

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
			'STREET' => $this->getField('street'),
			'BUILDING' => $this->getField('building'),
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
		return (string)$this->requireField('country');
	}

	public function getEntrance() : ?string
	{
		return $this->getField('entrance');
	}

	public function getZip() : string
	{
		return $this->requireField('zip');
	}

	public function getLocality() : string
	{
		return $this->requireField('locality');
	}

	public function getStreet() : ?string
	{
		return $this->getField('street');
	}

	public function getBuilding() : ?string
	{
		return $this->getField('building');
	}

	public function getFloor() : ?string
	{
		return $this->getField('floor');
	}

	public function getRoom() : ?string
	{
		return $this->getField('room');
	}

	public function getIntercom() : ?string
	{
		return $this->getField('intercom');
	}

	public function getComment() : ?string
	{
		return $this->getField('comment');
	}

	public function getCoordinates() : ?array
	{
		$result = $this->requireField('location');

		Assert::isArray($result, 'location');

		return $result;
	}

	public function getLat() : ?int
	{
		$result = $this->getCoordinates();

		return (int)$result['latitude'];
	}

	public function getLon() : ?int
	{
		$result = $this->getCoordinates();

		return (int)$result['longitude'];
	}
}