<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Main;
use Bitrix\Iblock;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class Catalog extends EntityReference\Catalog
{
	public function getIblock(string $siteId = null) : ?int
	{
		if (!Main\Loader::includeModule('iblock')) { return null; }

		$result = null;

		$query = Iblock\IblockTable::getList([
			'filter' => [
				'=TYPE.LANG_MESSAGE.LANGUAGE_ID' => LANGUAGE_ID,
				'=TYPE.ID' => 'catalog',
				'=LID' => $siteId
			],
			'select' => [
				'ID',
				'NAME',
				'TYPE_NAME' => 'TYPE.LANG_MESSAGE.NAME',
			],
			'limit' => 1
		]);

		if ($row = $query->fetch())
		{
			$result = $row['ID'];
		}

		return $result;
	}

	public function getEnumIblock(string $siteId = null) : array
	{
		if (!Main\Loader::includeModule('iblock')) { return []; }

		$result = [];

		$query = Iblock\IblockTable::getList([
			'filter' => [
				'=TYPE.LANG_MESSAGE.LANGUAGE_ID' => LANGUAGE_ID,
				'=TYPE.ID' => 'catalog',
				//'=LID' => $siteId
			],
			'select' => [
				'ID',
				'NAME',
				'TYPE_NAME' => 'TYPE.LANG_MESSAGE.NAME',
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