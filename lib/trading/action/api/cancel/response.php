<?php

namespace YandexPay\Pay\Trading\Action\Api\Cancel;

use YandexPay\Pay\Trading\Action\Api;

class Response extends Api\Reference\Response
{
	public function getOperation() : Api\Cancel\Dto\Operation
	{
		return $this->getChildModel('data.operation');
	}

	protected function modelMap() : array
	{
		return [
			'data.operation' => Api\Cancel\Dto\Operation::class,
		];
	}
}