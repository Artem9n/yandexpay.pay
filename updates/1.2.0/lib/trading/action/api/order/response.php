<?php

namespace YandexPay\Pay\Trading\Action\Api\Order;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Api;

class Response extends Api\Reference\Response
{
	public function getOrder() : Api\Order\Dto\Order
	{
		return $this->getChildModel('data.order');
	}

	protected function modelMap() : array
	{
		return [
			'data.order' => Api\Order\Dto\Order::class
		];
	}
}