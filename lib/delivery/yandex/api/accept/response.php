<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\Accept;

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
		$value = $this->requireField('data.delivery.created');
		$date = new \DateTime($value);
		$date->setTimezone((new \DateTime())->getTimeZone());

		return $date->format('d.m.Y H:i:s');
	}

	public function getDeliveryUpdated() : string
	{
		$value = $this->requireField('data.delivery.updated');
		$date = new \DateTime($value);
		$date->setTimezone((new \DateTime())->getTimeZone());

		return $date->format('d.m.Y H:i:s');
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