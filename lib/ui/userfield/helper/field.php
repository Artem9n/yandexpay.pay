<?php

namespace YandexPay\Pay\Ui\Userfield\Helper;

class Field
{
	public static function extendValue(array $userField, $value, array $row = null) : array
	{
		$defaults = [];

		if ($value !== null)
		{
			$defaults['VALUE'] = $value;
		}

		if ($row !== null)
		{
			$defaults['ENTITY_VALUE_ID'] = $row['ID'] ?? null;
			$defaults['ROW'] = $row;
		}

		return $userField + $defaults;
	}
}