<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

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

	public function getProps() : ?array
	{
		return $this->getField('props');
	}

	public function getId() : int
	{
		return (int)$this->requireField('id');
	}

	public function getQuantity() : int
	{
		return (float)$this->requireField('count');
	}

	public function getBasketId() : ?string
	{
		return $this->getField('basketId');
	}
}