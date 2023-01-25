<?php

namespace YandexPay\Pay\Data;

use Bitrix\Catalog\VatTable;

class Vat
{
	protected static $availableValues = [
		'VAT_0' => 5,
		'VAT_20' => 1,
		'VAT_10' => 2,
		'NO_VAT' => 6
	];

	protected static $vatList;

	public static function getVatList() : array
	{
		if (self::$vatList === null)
		{
			self::$vatList = static::loadVatList();
		}

		return self::$vatList;
	}

	protected static function loadVatList() : array
	{
		$result = [];

		$query = VatTable::getList();

		while ($vat = $query->fetch())
		{
			$result[$vat['ID']] = $vat['RATE'] / 100;
		}

		return $result;
	}

	/**
	 * @param float|null|string $rate
	 *
	 * @return int
	 */
	public static function convertForService($rate) : int
	{
		$rate *= 100;

		if ((int)$rate > 10)
		{
			$result = static::$availableValues['VAT_20'];
		}
		else if ((int)$rate === 10)
		{
			$result = static::$availableValues['VAT_10'];
		}
		else
		{
			$result = static::$availableValues['NO_VAT'];
		}

		return $result;
	}
}