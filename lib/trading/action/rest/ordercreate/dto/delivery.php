<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Dto;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Reference as ActionReference;

class Delivery extends ActionReference\Dto
{
	public function getId() : string
	{
		return (string)$this->requireField('courierOptionId');
	}

	public function getAmount() : float
	{
		return (float)$this->requireField('amount');
	}

	public function getCustomerChoiceDate() : ?string
	{
		return $this->getField('customerChoice.date');
	}
}