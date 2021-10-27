<?php

namespace YandexPay\Pay\Ui\UserField;

use Bitrix\Main;

class EnumerationType
{
	use Concerns\HasCompatibleExtends;

	public static function getCommonExtends() : string
	{
		return Main\UserField\Types\EnumType::class;
	}

	public static function getCompatibleExtends() : string
	{
		return \CUserTypeEnum::class;
	}

	public static function getUserTypeDescription()
	{
		$result = static::callParent('getUserTypeDescription');

		if (!empty($result['USE_FIELD_COMPONENT']))
		{
			$result['USE_FIELD_COMPONENT'] = false;
		}

		return $result;
	}

	public static function GetList($arUserField)
	{
		$values = (array)$arUserField['VALUES'];

		$result = new \CDBResult();
		$result->InitFromArray($values);

		return $result;
	}

	public static function getFilterHTML($arUserField, $arHtmlControl)
	{
		return static::callParent('getFilterHTML', [$arUserField, $arHtmlControl]);
	}

	public static function getFilterData($arUserField, $arHtmlControl)
	{
		return static::callParent('getFilterData', [$arUserField, $arHtmlControl]);
	}

	public static function getEditFormHTML($arUserField, $arHtmlControl)
	{
		if (!isset($arUserField['SETTINGS']['DISPLAY']))
		{
			$arUserField['SETTINGS']['DISPLAY'] = 'LIST';
		}

		return static::callParent('getEditFormHTML', [$arUserField, $arHtmlControl]);
	}

	public static function getEditFormHTMLMulty($userField, $htmlControl)
	{
		if (!isset($userField['SETTINGS']['DISPLAY']))
		{
			$userField['SETTINGS']['DISPLAY'] = 'LIST';
		}

		return static::callParent('getEditFormHTMLMulty', [$userField, $htmlControl]);
	}

	public static function getAdminListViewHTML($arUserField, $arHtmlControl)
	{
		return static::callParent('getAdminListViewHTML', [$arUserField, $arHtmlControl]);
	}

	public static function getAdminListViewHTMLMulty($arUserField, $arHtmlControl)
	{
		return static::callParent('getAdminListViewHTMLMulty', [$arUserField, $arHtmlControl]);
	}
}