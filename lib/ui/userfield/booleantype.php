<?php
namespace YandexPay\Pay\Ui\Userfield;

use Bitrix\Main;

class BooleanType extends Main\UserField\Types\BooleanType
{
	public const VALUE_TRUE = 1;
	public const VALUE_FALSE = 0;

	public static function getTableFieldDescription(bool $default = null) : array
	{
		$result = [
			'values' => [
				static::VALUE_FALSE,
				static::VALUE_TRUE,
			],
		];

		if ($default !== null)
		{
			$result['default_value'] = $default ? static::VALUE_TRUE : static::VALUE_FALSE;
		}

		return $result;
	}
}