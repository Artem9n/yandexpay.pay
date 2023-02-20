<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Site\AvailableStore;

use Bitrix\Sale;
use Bitrix\Catalog;

class All implements InterfaceStrategy
{
	public function resolve(array $stores, Sale\Order $order) : array
	{
		if (empty($stores)) { return []; }

		$basket = $order->getBasket();

		if ($basket === null) { return []; }

		$productIds = [];

		/** @var \Bitrix\Sale\BasketItem $item */
		foreach ($basket as $item)
		{
			$productIds[] = $item->getProductId();
		}

		if (empty($productIds)) { return []; }

		$result = [];

		$queryStoreProduct = Catalog\StoreProductTable::getList([
			'filter' => [
				'=STORE_ID' => array_keys($stores),
				'=PRODUCT_ID' => $productIds,
			],
			'select' => ['PRODUCT_ID', 'AMOUNT', 'STORE_ID'],
		]);

		$storeProducts = [];

		while ($product = $queryStoreProduct->fetch())
		{
			$storeProducts[$product['STORE_ID']][$product['PRODUCT_ID']] = $product['AMOUNT'];
		}

		foreach ($stores as $storeId => $store)
		{
			$isAvailable = true;
			$storage = $storeProducts[$storeId];

			if (!isset($storage)) { continue; }

			foreach ($productIds as $productId)
			{
				if (!isset($storage[$productId]) || $storage[$productId] <= 0)
				{
					$isAvailable = false;
					break;
				}
			}

			if ($isAvailable)
			{
				$result[$storeId] = $store;
			}
		}

		return $result;
	}
}