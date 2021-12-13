<?php
namespace YandexPay\Pay\Trading\Action\Incoming\OrderAccept;

use YandexPay\Pay\Trading\Action\Incoming;

class Delivery extends Incoming\Skeleton
{
	public function getId() : int
	{
		return (int)$this->requireField('id');
	}

	public function getLabel() : string
	{
		return (string)$this->requireField('label');
	}

	public function getAmount() : float
	{
		return (float)$this->requireField('amount');
	}

	public function getProvider() : string
	{
		return (string)$this->requireField('provider');
	}

	public function getCategory() : string
	{
		return (string)$this->requireField('category');
	}

	public function getDate() : int
	{
		return (int)$this->requireField('date');
	}
}