<?php

namespace YandexPay\Pay\Trading\Action\Api\Operation;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Api;

class Response extends Api\Reference\Response
{
	public function getOperation()
	{
		return $this->requireField('operation');
	}

	public function getOperationId()
	{
		return $this->requireField('operation.operationId');
	}
}