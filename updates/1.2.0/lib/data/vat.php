<?php

namespace YandexPay\Pay\Data;

class Vat
{
	protected static $availableValues = [
		'VAT_0' => 5,
		'VAT_20' => 1,
		'VAT_10' => 2,
		'NO_VAT' => 6
	];

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
			$result = static::$availableValues['VAT_0'];
		}

		return $result;
	}
}