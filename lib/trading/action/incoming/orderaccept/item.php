<?php
namespace YandexPay\Pay\Trading\Action\Incoming\OrderAccept;

use YandexPay\Pay\Trading\Action\Incoming\Skeleton;

class Item extends Skeleton
{
	public function getLabel() : string
	{
		return (string)$this->requireField('label');
	}

	public function getAmount() : float
	{
		return (float)$this->requireField('amount');
	}

	public function getId() : int
	{
		return (int)$this->requireField('id');
	}

	public function getQuantity() : int
	{
		return (float)$this->requireField('count');
	}
}