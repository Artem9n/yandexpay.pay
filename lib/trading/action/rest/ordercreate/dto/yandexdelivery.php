<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Dto;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action;

class YandexDelivery extends Delivery
{
	public function getId() : string
	{
		return (string)$this->requireField('yandexDeliveryOptionId');
	}

	public function getTitle() : string
	{
		return (string)$this->requireField('title');
	}

	public function getFromDateTime() : ?Main\Type\DateTime
	{
		$value = $this->getField('fromDatetime');

		if ($value === null) { return null; }

		$date = new Main\Type\DateTime($value, \DateTimeInterface::ATOM);

		$date->setDefaultTimeZone();

		return $date;
	}

	public function getToDatetime() : ?Main\Type\DateTime
	{
		$value = $this->getField('toDatetime');

		if ($value === null) { return null; }

		$date = new Main\Type\DateTime($value, \DateTimeInterface::ATOM);

		$date->setDefaultTimeZone();

		return $date;
	}
}