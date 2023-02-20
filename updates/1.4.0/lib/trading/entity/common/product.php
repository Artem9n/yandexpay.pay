<?php

namespace YandexPay\Pay\Trading\Entity\Common;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Iblock;

class Product extends EntityReference\Product
{
	use Concerns\HasMessage;

	public function productData(int $productId) : array
	{
		$products = $this->loadProducts([$productId], [ 'TYPE', 'AVAILABLE' ]);

		if (!isset($products[$productId]))
		{
			throw new Main\ArgumentException(static::getMessage('NOT_PRODUCT_DATA', [
				'#PRODUCT_ID#' => $productId
			]));
		}

		return $products[$productId];
	}

	public function isSku(array $product) : bool
	{
		return (int)$product['TYPE'] === Catalog\ProductTable::TYPE_SKU;
	}

	public function isOffer(array $product) : bool
	{
		return (int)$product['TYPE'] === Catalog\ProductTable::TYPE_OFFER;
	}

	public function searchProductId(int $offerId, int $iblockId = 0) : int
	{
		$products = \CCatalogSku::getProductList($offerId, $iblockId);

		if (empty($products[$offerId]))
		{
			throw new Main\ArgumentException(static::getMessage('NOT_PRODUCT_PARENT', [
				'#OFFER_ID#' => $offerId
			]));
		}

		return (int)$products[$offerId]['ID'];
	}

	public function searchOffers(int $productId, int $iblockId = 0, array $filter = []) : array
	{
		$filter += ['ACTIVE' => 'Y'];

		$offers = \CCatalogSku::getOffersList($productId, $iblockId, $filter, [ 'ID', 'AVAILABLE' ], [], [], [ 'AVAILABLE' => 'DESC', 'SORT' => 'ASC' ]);

		if (empty($offers[$productId]))
		{
			throw new Main\ArgumentException(static::getMessage('NOT_OFFERS', [
				'#PRODUCT_ID#' => $productId
			]));
		}

		return $offers[$productId];
	}

	public function selectOffer(int $iblockId, array $products, array $filter) : ?int
	{
		if (empty($products))
		{
			throw new Main\ArgumentException(static::getMessage('NOT_PRODUCTS'));
		}

		if (empty($filter))
		{
			$result = null;

			foreach ($products as $productId => $product)
			{
				if ($product['AVAILABLE'] === 'Y')
				{
					$result = $productId;
					break;
				}
				else if ($result === null)
				{
					$result = $productId;
				}
			}
		}
		else if (isset($filter['=ID']))
		{
			$idFilter = $filter['=ID'];

			if (!is_numeric($idFilter))
			{
				throw new Main\ArgumentException(static::getMessage('ERR_FILTER_ID'));
			}

			if (!isset($products[$idFilter]))
			{
				throw new Main\ArgumentException(static::getMessage('ERR_FILTER_ID_PRODUCTS'));
			}

			$result = (int)$idFilter;
		}
		else
		{
			$query = \CIBlockElement::GetList(
				[],
				[
					'IBLOCK_ID' => $iblockId,
					'=ID' => array_keys($products),
					$filter,
				],
				false,
				[ 'nTopCount' => 1 ],
				[ 'ID' ]
			);

			if ($row = $query->Fetch())
			{
				$result = (int)$row['ID'];
			}
			else
			{
				throw new Main\ArgumentException(static::getMessage('NOT_FOUND_OFFER', [
					'#PRODUCT_ID#' => implode(', ', array_keys($products))
				]));
			}
		}

		return $result;
	}

	public function getBasketData(array $productIds) : array
	{
		$elements = $this->loadElements($productIds, [ 'XML_ID', 'IBLOCK_ID', 'IBLOCK_XML_ID' => 'IBLOCK.XML_ID' ]);
		$products = $this->loadProducts($productIds, [ 'TYPE' ]);
		$offers = array_filter($products, static function($product) {
			return (int)$product['TYPE'] === Catalog\ProductTable::TYPE_OFFER;
		});
		$offerElements = array_intersect_key($elements, $offers);
		$offerParentMap = $this->loadOfferParentMap($offerElements);
		$offerProperties = $this->loadOfferProperties($offerElements);
		$parentIds = array_unique($offerParentMap);
		$parents = $this->loadElements($parentIds, [ 'XML_ID' ]);
		$result = [];
		$ratio = \Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($productIds);

		foreach ($productIds as $productId)
		{
			$element = $elements[$productId] ?? null;
			$properties = $offerProperties[$productId] ?? null;
			$product = $products[$productId] ?? null;
			$parent = null;

			if (isset($offerParentMap[$productId]))
			{
				$parentId = $offerParentMap[$productId];
				$parent = $parents[$parentId] ?? null;
			}

			$validationError = $this->validateElementBasketData($element, $product, $parent);
			$basketData = $this->mergeElementBasketData([
				$this->fillElementProperties($properties),
				$this->fillElementBasketXmlId($element, $parent)
			]);

			if ($validationError !== null)
			{
				$basketData['ERROR'] = $validationError;
			}

			if (isset($ratio[$productId]))
			{
				$basketData['RATIO'] = $ratio[$productId]['RATIO'];
			}

			$result[$productId] = $basketData;
		}

		return $result;
	}

	protected function loadElements(array $productIds, array $select = []) : array
	{
		$result = [];

		if (empty($productIds)) { return $result; }

		$query = Iblock\ElementTable::getList([
			'filter' => [ '=ID' => $productIds ],
			'select' => array_merge(
				[ 'IBLOCK_ID', 'ID', 'ACTIVE', 'ACTIVE_FROM', 'ACTIVE_TO' ],
				$select
			)
		]);

		while ($row = $query->Fetch())
		{
			$result[$row['ID']] = $row;
		}

		return $result;
	}

	protected function existsElements(array $productIds, bool $checkActive = false) : bool
	{
		$elements = $this->loadElements($productIds);
		$productMap = array_flip($productIds);
		$notExistsElements = array_diff_key($productMap, $elements);
		$result = true;

		if (!empty($notExistsElements))
		{
			$result = false;
		}
		else if ($checkActive)
		{
			foreach ($elements as $element)
			{
				if (!$this->isElementActive($element))
				{
					$result = false;
					break;
				}
			}
		}

		return $result;
	}

	public function loadProducts(array $productIds, array $select = []) : array
	{
		$result = [];

		if (empty($productIds)) { return $result; }

		$query = Catalog\ProductTable::getList([
			'filter' => [ '=ID' => $productIds ],
			'select' => array_merge([ 'ID' ], $select)
		]);

		while ($row = $query->fetch())
		{
			$result[$row['ID']] = $row;
		}

		return $result;
	}

	protected function getSetProducts(int $productId) : array
	{
		$result = [];
		$allSets = \CCatalogProductSet::getAllSetsByProduct($productId, \CCatalogProductSet::TYPE_SET);

		if (!empty($allSets))
		{
			$firstSet = reset($allSets);

			foreach ($firstSet['ITEMS'] as $setItem)
			{
				$setItemProductId = (int)$setItem['ITEM_ID'];
				$setItemOwnerId = (int)$setItem['OWNER_ID'];

				if ($setItemProductId !== $setItemOwnerId)
				{
					$result[] = $setItemProductId;
				}
			}
		}

		return $result;
	}

	protected function loadOfferParentMap(array $offers) : array
	{
		$offersByIblock = $this->groupElementsByIblock($offers);
		$result = [];

		foreach ($offersByIblock as $iblockId => $offerIds)
		{
			$offerProductData = \CCatalogSku::getProductList($offerIds, $iblockId);

			foreach ($offerProductData as $offerId => $productData)
			{
				$result[$offerId] = (int)$productData['ID'];
			}
		}

		return $result;
	}

	protected function loadOfferProperties(array $elements) : array
	{
		$result = [];

		if (!$this->isPropertyFeatureEnabled()) { return $result; }

		$elementsByIblock = $this->groupElementsByIblock($elements);

		foreach ($elementsByIblock as $iblockId => $elementIds)
		{
			$propertyIds = $this->getFeatureProperties($iblockId);

			if (empty($propertyIds)) { continue; }

			$iblockCatalog = \CCatalogSku::GetInfoByIBlock($iblockId);

			if (
				empty($iblockCatalog['PRODUCT_IBLOCK_ID'])
				|| $iblockCatalog['CATALOG_TYPE'] !== \CCatalogSku::TYPE_OFFERS
			)
			{
				continue;
			}

			foreach ($elementIds as $elementId)
			{
				$result[$elementId] = \CIBlockPriceTools::GetOfferProperties(
					$elementId,
					$iblockCatalog['PRODUCT_IBLOCK_ID'],
					$propertyIds
				);
			}
		}

		return $result;
	}

	protected function isPropertyFeatureEnabled() : bool
	{
		return (
			class_exists(Catalog\Product\PropertyCatalogFeature::class)
			&& Catalog\Product\PropertyCatalogFeature::isEnabledFeatures()
		);
	}

	protected function getFeatureProperties(int $iblockId) : ?array
	{
		return Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes($iblockId, [ 'CODE' => 'Y' ]);
	}

	protected function groupElementsByIblock(array $elements) : array
	{
		$result = [];

		foreach ($elements as $element)
		{
			$iblockId = (int)$element['IBLOCK_ID'];

			if (!isset($result[$iblockId]))
			{
				$result[$iblockId] = [];
			}

			$result[$iblockId][] = (int)$element['ID'];
		}

		return $result;
	}

	protected function validateElementBasketData(array $element = null, array $product = null, array $parent = null) : ?Main\Error
	{
		$result = null;

		try
		{
			if ($element === null || !$this->isElementActive($element))
			{
				$message = self::getMessage('BASKET_ERR_NO_IBLOCK_ELEMENT'); // todo lang file
				throw new Main\SystemException($message);
			}

			if ($product === null)
			{
				$message = self::getMessage('BASKET_ERR_NO_PRODUCT');
				throw new Main\SystemException($message);
			}

			$productType = (int)$product['TYPE'];

			if (
				($productType === Catalog\ProductTable::TYPE_SKU || $productType === Catalog\ProductTable::TYPE_EMPTY_SKU)
				&& Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') !== 'Y'
			)
			{
				$message = self::getMessage('BASKET_ERR_CANNOT_ADD_SKU');
				throw new Main\SystemException($message);
			}

			if (
				$productType === Catalog\ProductTable::TYPE_OFFER
				&& ($parent === null || !$this->isElementActive($parent))
			)
			{
				$message = self::getMessage('BASKET_ERR_PRODUCT_BAD_TYPE');
				throw new Main\SystemException($message);
			}

			if ($productType === Catalog\ProductTable::TYPE_SET)
			{
				$setProducts = $this->getSetProducts($product['ID']);

				if (empty($setProducts))
				{
					$message = self::getMessage('BASKET_ERR_NO_PRODUCT_SET');
					throw new Main\SystemException($message);
				}

				if (!$this->existsElements($setProducts, true))
				{
					$message = self::getMessage('BASKET_ERR_NO_PRODUCT_SET_ITEMS');
					throw new Main\SystemException($message);
				}
			}
		}
		catch (Main\SystemException $exception)
		{
			$result = new Main\Error($exception->getMessage(), $exception->getCode());
		}

		return $result;
	}

	protected function mergeElementBasketData(array $dataList) : array
	{
		$result = array_shift($dataList);
		$multipleFields = [
			'PROPS',
		];

		foreach ($dataList as $data)
		{
			foreach ($multipleFields as $multipleField)
			{
				if (
					isset($data[$multipleField])
					&& array_key_exists($multipleField, $result)
				)
				{
					$result[$multipleField] = array_merge(
						(array)$result[$multipleField],
						(array)$data[$multipleField]
					);
				}
			}

			$result += $data;
		}

		return $result;
	}

	protected function fillElementProperties(array $properties = null) : array
	{
		$result = [];

		if (!empty($properties))
		{
			$result['PROPS'] = (array)$properties;
		}

		return $result;
	}

	protected function fillElementBasketXmlId(array $element = null, array $parent = null) : array
	{
		$result = [
			'PROPS' => [],
		];
		$productXmlId = isset($element['XML_ID']) ? (string)$element['XML_ID'] : '';
		$catalogXmlId = isset($element['IBLOCK_XML_ID']) ? (string)$element['IBLOCK_XML_ID'] : '';

		if ($productXmlId !== '')
		{
			if ($parent !== null && mb_strpos($productXmlId, '#') === false)
			{
				$productXmlId = $parent['XML_ID'] . '#' . $productXmlId;
			}

			$result['PRODUCT_XML_ID'] = $productXmlId;
			$result['PROPS'][] = [
				'NAME' => 'Product XML_ID',
				'CODE' => 'PRODUCT.XML_ID',
				'VALUE' => $productXmlId,
			];
		}

		if ($catalogXmlId !== '')
		{
			$result['CATALOG_XML_ID'] = $element['IBLOCK_XML_ID'];
			$result['PROPS'][] = [
				'NAME' => 'Catalog XML_ID',
				'CODE' => 'CATALOG.XML_ID',
				'VALUE' => $element['IBLOCK_XML_ID'],
			];
		}

		return $result;
	}

	protected function isElementActive(array $element) : bool
	{
		$result = true;

		if ($element['ACTIVE'] !== 'Y')
		{
			$result = false;
		}
		else if (
			$element['ACTIVE_FROM'] instanceof Main\Type\Date
			&& $element['ACTIVE_FROM']->getTimestamp() > time()
		)
		{
			$result = false;
		}
		else if (
			$element['ACTIVE_TO'] instanceof Main\Type\Date
			&& $element['ACTIVE_TO']->getTimestamp() < time()
		)
		{
			$result = false;
		}

		return $result;
	}
}