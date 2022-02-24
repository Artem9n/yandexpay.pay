<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Main;
use Bitrix\Iblock;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class Catalog extends EntityReference\Catalog
{
	public function getIblock(string $siteId = null) : ?int
	{
		if (!Main\Loader::includeModule('catalog')) { return null; }

		$result = null;

		$query = CatalogIblockTable::getList([
			'order' => ['IBLOCK_ID' => 'ASC'],
			'filter' => [
				'=IBLOCK.LID' => $siteId,
				'=IBLOCK.TYPE.LANG_MESSAGE.LANGUAGE_ID' => LANGUAGE_ID,
				//'%IBLOCK.TYPE.ID' => 'catalog'
			],
			'select' => [
				'ID' => 'IBLOCK.ID'
			],
			'limit' => 1,
		]);

		if ($row = $query->fetch())
		{
			$result = $row['ID'];
		}

		return $result;
	}

	public function getEnumIblock(string $siteId = null) : array
	{
		if (!Main\Loader::includeModule('catalog')) { return []; }

		$result = [];

		$query = CatalogIblockTable::getList([
			'order' => ['IBLOCK_ID' => 'ASC'],
			'filter' => [
				'=IBLOCK.TYPE.LANG_MESSAGE.LANGUAGE_ID' => LANGUAGE_ID,
				//'%IBLOCK.TYPE.ID' => 'catalog'
			],
			'select' => [
				'ID' => 'IBLOCK.ID',
				'NAME' => 'IBLOCK.NAME',
				'TYPE_NAME' => 'IBLOCK.TYPE.LANG_MESSAGE.NAME',
			],
		]);

		while ($row = $query->fetch())
		{
			$result[] = [
				'ID' => $row['ID'],
				'VALUE' => sprintf('[%s] %s', $row['ID'], $row['NAME']),
				'GROUP' => $row['TYPE_NAME'],
			];
		}

		return $result;
	}
}