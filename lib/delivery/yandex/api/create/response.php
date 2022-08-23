<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\Create;

use Bitrix\Main\SystemException;
use YandexPay\Pay\Trading\Action\Api;

class Response extends Api\Reference\Response
{
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
			'CREATED' => $this->getDeliveryCreated(),
			'UPDATED' => $this->getDeliveryUpdated(),
		];
	}

	public function validate() : void
	{
		parent::validate();

		if ($this->getDeliveryStatus() === static::DELIVERY_STATUS_FAIL)
		{
			throw new SystemException('Доставка завершилась ошибкой'); //todo message
		}
	}
}