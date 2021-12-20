<?php

namespace YandexPay\Pay\Ui\UserField\Helper;

use YandexPay\Pay\Ui\UserField\Registry;

class Field
{
	public static function extend(array $field, string $name = null) : array
	{
		$field += [
			'MULTIPLE' => 'N',
			'EDIT_IN_LIST' => 'Y',
			'EDIT_FORM_LABEL' => $field['NAME'],
			'SETTINGS' => [],
		];

		if ($name !== null)
		{
			$field['FIELD_NAME'] = $name;
		}

		if (!isset($field['USER_TYPE']) && isset($field['TYPE']))
		{
			$field['USER_TYPE'] = Registry::getUserType($field['TYPE']);
		}

		return $field;
	}

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