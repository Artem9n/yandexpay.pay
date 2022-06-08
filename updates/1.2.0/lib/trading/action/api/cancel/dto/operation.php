<?php

namespace YandexPay\Pay\Trading\Action\Api\Cancel\Dto;

use YandexPay\Pay\Trading\Action;

class Operation extends Action\Reference\Dto
{
	public function getStatus() : string
	{
		return $this->requireField('status');
	}

	public function getOperationType() : string
	{
		return $this->requireField('operationType');
	}
}