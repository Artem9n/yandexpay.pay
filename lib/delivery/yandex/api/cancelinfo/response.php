<?php

namespace YandexPay\Pay\Delivery\Yandex\Api\CancelInfo;

use YandexPay\Pay\Trading\Action\Api;

class Response extends Api\Reference\Response
{
	public function getCancelState() : string
	{
		return (string)$this->requireField('data.cancelState');
	}
}