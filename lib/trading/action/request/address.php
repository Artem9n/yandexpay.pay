<?php
namespace YandexPay\Pay\Trading\Action\Request;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class Address extends Model
{
	public function getMeaningfulAddress(array $skipAdditionalTypes = [])
	{
		$values = $this->getAddressValues();

		return $this->combineAddress($values, $skipAdditionalTypes);
	}

	public function getCountry() : string
	{
		$result = $this->getField('country');

		Assert::notNull($result, 'country');
		Assert::isString($result, 'country');

		return $result;
	}

	public function getRegion() : string
	{
		$result = $this->getField('regionId');

		Assert::notNull($result, 'regionId');
		Assert::isString($result, 'regionId');

		return $result;
	}

	public function getCity() : string
	{
		$result = $this->getField('city');

		Assert::notNull($result, 'city');
		Assert::isString($result, 'city');

		return $result;
	}

	public function getStreet() : ?string
	{
		$result = $this->getField('street');

		if ($result === null) { return null; }

		Assert::isString($result, 'street');

		return $result;
	}

	public function getHouse() : ?string
	{
		$result = $this->getField('house');

		if ($result === null) { return null; }

		Assert::isString($result, 'house');

		return $result;
	}

	public function getCoodinates() : ?Model
	{
		$result = $this->getField('coordinates');

		if ($result === null) { return null; }

		Assert::isArray($result, 'coordinates');

		return Address\Coordinates::initialize($result);
	}
}