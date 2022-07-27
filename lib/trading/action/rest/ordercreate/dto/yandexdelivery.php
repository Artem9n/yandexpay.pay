<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Dto;

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

	public function getFromDateTime() : ?\DateTime
	{
		$date = $this->getField('fromDatetime');

		if ($date === null) { return null; }

		return new \DateTime($date);
	}

	public function getToDatetime() : ?\DateTime
	{
		$date = $this->getField('toDatetime');

		if ($date === null) { return null; }

		return new \DateTime($date);
	}
}