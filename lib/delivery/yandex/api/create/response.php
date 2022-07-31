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

	public function validate() : void
	{
		parent::validate();

		if ($this->getDeliveryStatus() === static::DELIVERY_STATUS_FAIL)
		{
			throw new SystemException('Доставка завершилась ошибкой'); //todo message
		}
	}
}