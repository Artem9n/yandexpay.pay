<?php
namespace YandexPay\Pay\Trading\Action\Rest\Dto\Cart;

use YandexPay\Pay\Trading\Action\Reference as ActionReference;

class Item extends ActionReference\Dto
{
	public function getId() : string
	{
		return (string)$this->requireField('productId');
	}

	public function getProductId() : string
	{
		[$productId, $basketId] = explode(':', $this->getId());

		return $productId;
	}

	public function getBasketId() : ?string
	{
		[$productId, $basketId] = explode(':', $this->getId());

		return $basketId;
	}

	public function getCount() : ?float
	{
		return $this->getField('quantity.count');
	}

	public function getAmount() : float
	{
		return $this->getField('total');
	}
}