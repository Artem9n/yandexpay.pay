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

	public function getCustomerChoiceTime() : ?array
	{
		return $this->getField('customerChoice.time');
	}

	public function getScheduleType() : string
	{
		return $this->requireField('type');
	}

	public function getPlainToDate() : ?string
	{
		return $this->getField('toDate');
	}

	public function getPlainFromDate() : ?string
	{
		return $this->getField('fromDate');
	}

	public function getPlainFromTime() : ?string
	{
		return $this->getField('fromTime');
	}

	public function getPlainToTime() : ?string
	{
		return $this->getField('toTime');
	}
}