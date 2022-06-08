<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderPayment\Dto;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Reference as ActionReference;

class Operation extends ActionReference\Dto
{
	public function getId() : string
	{
		return (string)$this->requireField('operationId');
	}

	public function getExternalId() : ?string
	{
		return (string)$this->getField('externalOperationId');
	}

	public function getOrderId() : string
	{
		return (string)$this->requireField('orderId');
	}

	public function getStatus() : string
	{
		return (string)$this->requireField('status');
	}

	public function getType() : string
	{
		return (string)$this->requireField('operationType');
	}
}