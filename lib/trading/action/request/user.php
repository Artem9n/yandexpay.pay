<?php
namespace YandexPay\Pay\Trading\Action\Request;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

//lib/trading/service/marketplacedbs/model/cart/delivery/address.php

class User extends Model
{
	public function getEmail() : string
	{
		$result = $this->getField('email');

		Assert::notNull($result, 'email');
		Assert::isString($result, 'email');

		return $result;
	}

	public function getPhone() : ?string
	{
		$result = $this->getField('phone');

		if ($result === null) { return null; }

		Assert::isString($result, 'phone');

		return $result;
	}

	public function getFirstName() : ?string
	{
		$result = $this->getField('firstName');

		if ($result === null) { return null; }

		Assert::isString($result, 'firstName');

		return $result;
	}

	public function getLastName() : ?string
	{
		$result = $this->getField('lastName');

		if ($result === null) { return null; }

		Assert::isString($result, 'lastName');

		return $result;
	}

	public function getSecondName() : ?string
	{
		$result = $this->getField('secondName');

		if ($result === null) { return null; }

		Assert::isString($result, 'secondName');

		return $this->getField('secondName');
	}

	public function getMeaningfulValues() : array
	{
		return array_filter([
			'LAST_NAME' => $this->getLastName(),
			'FIRST_NAME' => $this->getFirstName(),
			'MIDDLE_NAME' => $this->getSecondName(),
			'PHONE' => $this->getPhone(),
			'EMAIL' => $this->getEmail()
		]);
	}
}