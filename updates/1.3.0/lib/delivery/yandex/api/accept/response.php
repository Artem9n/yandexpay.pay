<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\Accept;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Api;

class Response extends Api\Reference\Response
{
	public function getDeliveryStatus() : string
	{
		return (string)$this->requireField('data.delivery.status');
	}

	public function getDeliveryPrice() : float
	{
		return (float)$this->requireField('data.delivery.price');
	}

	public function getDeliveryActualPrice() : float
	{
		return (float)$this->requireField('data.delivery.actualPrice');
	}

	public function getDeliveryCreated() : string
	{
		$date = Main\Type\DateTime::createFromPhp(
			\DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $this->requireField('data.delivery.created'))
		);

		$date->setDefaultTimeZone();

		return (string)$date;
	}

	public function getDeliveryUpdated() : string
	{
		$date = Main\Type\DateTime::createFromPhp(
			\DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $this->requireField('data.delivery.updated'))
		);

		$date->setDefaultTimeZone();

		return (string)$date;
	}

	public function getDeliveryData() : array
	{
		return [
			'PRICE' => $this->getDeliveryPrice(),
			'ACTUAL_PRICE' => $this->getDeliveryActualPrice(),
			'CREATED' => $this->getDeliveryCreated(),
			'UPDATED' => $this->getDeliveryUpdated(),
		];
	}
}