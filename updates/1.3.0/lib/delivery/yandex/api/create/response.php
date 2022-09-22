<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\Create;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Api;

class Response extends Api\Reference\Response
{
	use Concerns\HasMessage;

	public const DELIVERY_STATUS_SUCCESS = 'SUCCESS';
	public const DELIVERY_STATUS_FAIL = 'FAILED';

	public function getDeliveryStatus() : string
	{
		return (string)$this->requireField('data.delivery.status');
	}

	public function getDeliveryPrice() : float
	{
		return (float)$this->requireField('data.delivery.price');
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
			'CREATED' => $this->getDeliveryCreated(),
			'UPDATED' => $this->getDeliveryUpdated(),
		];
	}

	public function validate() : void
	{
		parent::validate();

		if ($this->getDeliveryStatus() === static::DELIVERY_STATUS_FAIL)
		{
			throw new Main\SystemException(self::getMessage('FAIL'));
		}
	}
}