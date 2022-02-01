<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

use YandexPay\Pay\Reference\Common\Collection;

/**
 * @property Item[] $collection
 */
class Items extends Collection
{
	public static function getItemReference() : string
	{
		return Item::class;
	}

	public function getProducts() : array
	{
		$result = [];

		foreach ($this->collection as $model)
		{
			$result[] = $model;
		}

		return $result;
	}
}