<?php
namespace YandexPay\Pay\Trading\Action\Incoming\OrderAccept;

use YandexPay\Pay\Trading\Action\Incoming;

class User extends Incoming\Skeleton
{
	public function getEmail() : string
	{
		return $this->requireField('email');
	}

	public function getPhone() : ?string
	{
		return $this->getField('phone');
	}

	public function getFirstName() : ?string
	{
		return $this->getField('firstName');
	}

	public function getLastName() : ?string
	{
		return $this->getField('lastName');
	}

	public function getSecondName() : ?string
	{
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