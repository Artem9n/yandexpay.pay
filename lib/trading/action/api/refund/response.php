<?php

namespace YandexPay\Pay\Trading\Action\Api\Refund;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Api;

class Response extends Api\Reference\Response
{
	public function getOperation() : Api\Refund\Dto\Operation
	{
		return $this->getChildModel('data.operation');
	}

	protected function modelMap() : array
	{
		return [
			'data.operation' => Api\Refund\Dto\Operation::class,
		];
	}
}