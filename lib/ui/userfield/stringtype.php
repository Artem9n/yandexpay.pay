<?php

namespace YandexPay\Pay\Ui\UserField;

use Bitrix\Main;

class StringType
{
	use Concerns\HasCompatibleExtends;

	public static function getCommonExtends() : string
	{
		return Main\UserField\Types\StringType::class;
	}

	public static function getCompatibleExtends() : string
	{
		return \CUserTypeString::class;
	}

	protected static function includeMessages() : void
	{
		Main\Localization\Loc::loadMessages(__FILE__);
	}

	public static function getUserTypeDescription()
	{
		return static::callParent('getUserTypeDescription');
	}

	public static function checkFields($arUserField, $value)
	{
		return static::callParent('checkFields', [$arUserField, $value]);
	}

	public static function getFilterHTML($userField, $htmlControl)
	{
		return static::callParent('getFilterHTML', [$userField, $htmlControl]);
	}

	public static function getFilterData($arUserField, $arHtmlControl)
	{
		return static::callParent('getFilterData', [$arUserField, $arHtmlControl]);
	}

	public static function getEditFormHtmlMulty($userField, $htmlControl)
	{
		return static::callParent('getEditFormHtmlMulty', [$userField, $htmlControl]);
	}

	public static function getEditFormHTML($userField, $htmlControl) : string
	{
		$attributes = Helper\Attributes::extractFromSettings($userField['SETTINGS']);

		$result = static::getEditInput($userField, $htmlControl);
		return Helper\Attributes::insert($result, $attributes);
	}

	public static function GetAdminListViewHtml($userField, $htmlControl) : string
	{
		$value = (string)Helper\Value::asSingle($userField, $htmlControl);

		return $value !== '' ? $value : '&nbsp;';
	}

	protected static function getEditInput($userField, $htmlControl) : string
	{
		if ($userField['ENTITY_VALUE_ID'] < 1 && (string)$userField['SETTINGS']['DEFAULT_VALUE'] !== '')
		{
			$htmlControl['VALUE'] = htmlspecialcharsbx($userField['SETTINGS']['DEFAULT_VALUE']);
		}

		if ($userField['SETTINGS']['ROWS'] < 2)
		{
			$htmlControl['VALIGN'] = 'middle';
			$attributes = [
				'type' => 'text',
				'name' => $htmlControl['NAME'],
			];
			$attributes += array_filter([
				'size' => isset($userField['SETTINGS']['SIZE']) ? (int)$userField['SETTINGS']['SIZE'] : null,
				'maxlength' => isset($userField['SETTINGS']['MAX_LENGTH']) ? (int)$userField['SETTINGS']['MAX_LENGTH'] : null,
				'disabled' => $userField['EDIT_IN_LIST'] !== 'Y',
				'data-multiple' => $userField['MULTIPLE'] !== 'N',
			]);
			
			return sprintf(
				'<input %s value="%s" />',
				Helper\Attributes::stringify($attributes),
				$htmlControl['VALUE']
			);
		}

		$attributes = [
			'name' => $htmlControl['NAME'],
		];
		$attributes += array_filter([
			'cols' => isset($userField['SETTINGS']['SIZE']) ? (int)$userField['SETTINGS']['SIZE'] : null,
			'rows' => isset($userField['SETTINGS']['ROWS']) ? (int)$userField['SETTINGS']['ROWS'] : null,
			'maxlength' => isset($userField['SETTINGS']['MAX_LENGTH']) ? (int)$userField['SETTINGS']['MAX_LENGTH'] : null,
			'disabled' => $userField['EDIT_IN_LIST'] !== 'Y',
			'data-multiple' => $userField['MULTIPLE'] !== 'N',
		]);

		return sprintf(
			'<textarea %s>%s</textarea>',
			Helper\Attributes::stringify($attributes),
			$htmlControl['VALUE']
		);
	}
}