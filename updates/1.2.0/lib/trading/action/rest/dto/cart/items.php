<?php
namespace YandexPay\Pay\Trading\Action\Rest\Dto\Cart;

use YandexPay\Pay\Reference\Common\Collection;

/**
 * @method Item[] getIterator()
 */
class Items extends Collection
{
	public static function getItemReference() : string
	{
		return Item::class;
	}
}