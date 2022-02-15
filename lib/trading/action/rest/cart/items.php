<?php
namespace YandexPay\Pay\Trading\Action\Rest\Cart;

use YandexPay\Pay\Reference\Common\Collection;

class Items extends Collection
{
	public static function getItemReference() : string
	{
		return Item::class;
	}
}