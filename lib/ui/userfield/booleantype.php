<?php

namespace YandexPay\Pay\Ui\Userfield;

use Bitrix\Main;

class BooleanType
{
	public const VALUE_TRUE = 1;
	public const VALUE_FALSE = 0;

	use Concerns\HasCompatibleExtends;

	public static function getCommonExtends() : string
	{
		return Main\UserField\Types\BooleanType::class;
	}

	public static function getCompatibleExtends() : string
	{
		return \CUserTypeBoolean::class;
	}

	public static function getUserTypeDescription()
	{
		return static::callParent('getUserTypeDescription');
	}

	public static function OnBeforeSave($arUserField, $value)
	{
		return static::callParent('OnBeforeSave', [$arUserField, $value]);
	}

	public static function getFilterHTML($arUserField, $arHtmlControl)
	{
		return static::callParent('getFilterHTML', [$arUserField, $arHtmlControl]);
	}

	public static function getFilterData($arUserField, $arHtmlControl)
	{
		return static::callParent('getFilterData', [$arUserField, $arHtmlControl]);
	}

	public static function getAdminListViewHTML($userField, $htmlControl) : string
	{
		return static::callParent('getAdminListViewHTML', [$userField, $htmlControl]);
	}

	public static function getEditFormHTML($userField, $htmlControl) : string
	{
		return static::callParent('getEditFormHTML', [$userField, $htmlControl]);
	}

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