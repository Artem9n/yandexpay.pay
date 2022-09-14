<?php

namespace YandexPay\Pay\Trading\Entity\Common;

use Bitrix\Main;
use Bitrix\Catalog;
use YandexPay\Pay;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Settings\Options;

class Store extends Pay\Trading\Entity\Reference\Store
{
	protected $userFields;

	public function getFields(string $behavior = null) : array
	{
		if ($behavior === static::FIELD_BEHAVIOR_WAREHOUSE)
		{
			return $this->getUserFieldsForType(Pay\Ui\UserField\WarehouseType::USER_TYPE_ID);
		}

		if ($behavior === static::FIELD_BEHAVIOR_CONTACT)
		{
			return $this->getUserFieldsForType(Pay\Ui\UserField\UserType::USER_TYPE_ID);
		}

		if ($behavior === static::FIELD_BEHAVIOR_SCHEDULE)
		{
			return $this->getUserFieldsForType(Pay\Ui\UserField\ScheduleType::USER_TYPE_ID);
		}

		throw new Main\NotImplementedException(sprintf('% behavior not implementd', $behavior));
	}

	protected function getUserFieldsForType(string $userTypeId) : array
	{
		$userFields = $this->getUserFields();

		$result = [];

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

	protected function getUserFields() : array
	{
		if ($this->userFields === null)
		{
			$this->userFields = $this->loadUserFields();
		}

		return $this->userFields;
	}

	protected function loadUserFields() : array
	{
		global $USER_FIELD_MANAGER;

		return $USER_FIELD_MANAGER->GetUserFields('CAT_STORE', 0, LANGUAGE_ID);
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
		$basketProducts = [];

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$productId = $basketItem->getProductId();

			if (isset($basketProducts[$productId]))
			{
				$basketProducts[$productId] += $basketItem->getQuantity();
				continue;
			}

			$basketProducts[$productId] = $basketItem->getQuantity();
		}

		if (empty($basketProducts)) { return $result; }

		$storesProduct = $this->getStoresProduct(array_keys($basketProducts));

		foreach ($storesProduct as $storeId => $storeProducts)
		{
			$notAvailable = [];

			foreach ($basketProducts as $productId => $quantity)
			{
				if (
					!isset($storeProducts[$productId])
					|| $quantity > $storeProducts[$productId]
				)
				{
					$notAvailable[] = $productId;
				}
			}

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
			'select' => [ 'ID', 'STORE_ID', 'PRODUCT_ID', 'AMOUNT' ],
		]);

		while ($product = $query->fetch())
		{
			$result[$product['STORE_ID']][$product['PRODUCT_ID']] = (float)$product['AMOUNT'];
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