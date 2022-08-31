<?php

namespace YandexPay\Pay\Trading\Entity\Common;

use Bitrix\Main;
use Bitrix\Catalog;
use YandexPay\Pay;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Settings\Options;

class Store extends Pay\Trading\Entity\Reference\Store
{
	public function getFields(string $behavior = null) : array
	{
		if ($behavior === static::FIELD_BEHAVIOR_WAREHOUSE)
		{
			return $this->getUserFields(Pay\Ui\UserField\WarehouseType::USER_TYPE_ID);
		}

		if ($behavior === static::FIELD_BEHAVIOR_CONTACT)
		{
			return $this->getUserFields(Pay\Ui\UserField\UserType::USER_TYPE_ID);
		}

		throw new Main\NotImplementedException(sprintf('% behavior not implementd', $behavior));
	}

	protected function getUserFields(string $userTypeId) : array
	{
		global $USER_FIELD_MANAGER;

		$result = [];

		$userFields = $USER_FIELD_MANAGER->GetUserFields('CAT_STORE', 0, LANGUAGE_ID);

		if (empty($userFields)) { return $result; }

		foreach ($userFields as $code => $values)
		{
			if ($values['USER_TYPE_ID'] !== $userTypeId) { continue; }

			$result[] = [
				'ID' => $code,
				'VALUE' => $values['USER_TYPE']['DESCRIPTION'],
			];
		}

		return $result;
	}

	public function expressStrategyEnum() : array
	{
		$result = [];

		foreach (Express\Registry::types() as $type)
		{
			$strategy = Express\Registry::make($type);

			$result[] = [
				'ID' => $type,
				'VALUE' => $strategy->title(),
			];
		}

		return $result;
	}

	public function expressStrategy(string $type) : Express\AbstractStrategy
	{
		return Express\Registry::make($type);
	}

	public function available(EntityReference\Order $order) : array
	{
		$result = [];

		$basket = $order->getBasket();
		$productIds = [];

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$productIds[] = $basketItem->getProductId();
		}

		if (empty($productIds)) { return $result; }

		$storesProduct = $this->getStoresProduct($productIds);

		foreach ($storesProduct as $storeId => $products)
		{
			$notAvailable = array_diff($productIds, $products);

			if (!empty($notAvailable)) { continue; }

			$result[] = $storeId;
		}

		return $result;
	}

	protected function getStoresProduct(array $productIds) : array
	{
		$result = [];

		$query = Catalog\StoreProductTable::getList([
			'filter' => [
				'=PRODUCT_ID' => $productIds,
				'>AMOUNT' => 0,
			],
			'select' => [ 'ID', 'STORE_ID', 'PRODUCT_ID' ],
		]);

		while ($product = $query->fetch())
		{
			$result[$product['STORE_ID']][] = $product['PRODUCT_ID'];
		}

		return $result;
	}

	public function warehouse(int $storeId, string $fieldName) : Options\Warehouse
	{
		$result = new Options\Warehouse();

		$query = Catalog\StoreTable::getList([
			'filter' => [
				'=ID' => $storeId,
			],
			'select' => ['ID', $fieldName],
			'limit' => 1,
		]);

		if ($store = $query->fetch())
		{
			$values = unserialize($store[$fieldName], [ 'allowed_classes' => false ]);

			$result->setValues($values);
		}

		return $result;
	}

	public function contact(int $storeId, string $fieldName) : ?int
	{
		$result = null;

		$query = Catalog\StoreTable::getList([
			'filter' => [
				'=ID' => $storeId,
			],
			'select' => ['ID', $fieldName],
			'limit' => 1,
		]);

		if ($store = $query->fetch())
		{
			$result = $store[$fieldName];
		}

		return $result;
	}
}