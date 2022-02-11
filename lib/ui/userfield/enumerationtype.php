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

		if ($arUserField['ENTITY_VALUE_ID'] < 1 && (string)$arUserField['SETTINGS']['DEFAULT_VALUE'] !== '')
		{
			$arHtmlControl['VALUE'] = $arUserField['SETTINGS']['DEFAULT_VALUE'];
		}
		else if ($arHtmlControl['VALUE'] === '' && array_key_exists('VALUE', $arUserField) && $arUserField['VALUE'] === null)
		{
			$arHtmlControl['VALUE'] = $arUserField['SETTINGS']['DEFAULT_VALUE'] ?? null;
		}

		$settings = static::makeSelectViewSettings($arUserField);
		$enum = call_user_func([ $arUserField['USER_TYPE']['CLASS_NAME'], 'getList'], $arUserField);

		$result = '<select name="'.$arHtmlControl["NAME"]. '">';

		if ($settings['ALLOW_NO_VALUE'] === 'Y')
		{
			$noValueCaption =
				(string)$settings['CAPTION_NO_VALUE'] !== ''
					? $settings['CAPTION_NO_VALUE']
					: '';

			if (!empty($noValueCaption))
			{
				$result .= '<option value="0">' . htmlspecialcharsbx($noValueCaption) . '</option>';
			}
		}

		$useDefaultValue = ((string)$arHtmlControl['VALUE'] === '');
		$valueMap = static::getValueMap($arHtmlControl['VALUE']);

		foreach (static::toArray($enum) as $option)
		{
			$isSelected = $useDefaultValue ? $option['DEF'] === 'Y' : isset($valueMap[$option['ID']]);

			$result .=
				'<option value="' . $option['ID'] . '" ' . ($isSelected ? 'selected' : '') . '>'
				. $option['VALUE']
				. '</option>';
		}

		return $result;
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
		$result = '&nbsp;';

		if (!empty($arHtmlControl['VALUE']))
		{
			$value = $arHtmlControl['VALUE'];
			$query = call_user_func([ $arUserField['USER_TYPE']['CLASS_NAME'], 'getList' ], $arUserField);
			$enum = $query ? static::toArray($query) : [];
			$enumMap = array_column($enum, 'VALUE', 'ID');

			if (isset($enumMap[$value]))
			{
				$result = $enumMap[$value];
			}
			else if (
				isset($arUserField['SETTINGS']['DESCRIPTION_FIELD'])
				&& !empty($arUserField['ROW'][$arUserField['SETTINGS']['DESCRIPTION_FIELD']])
			)
			{
				$result = $arUserField['ROW'][$arUserField['SETTINGS']['DESCRIPTION_FIELD']];
			}
			else
			{
				$result = sprintf('[%s]', $value);
			}

			$result = static::htmlEscape($result);
		}

		return $result;
	}

	public static function getAdminListViewHTMLMulty($arUserField, $arHtmlControl)
	{
		$result = '&nbsp;';

		if (!empty($arHtmlControl['VALUE']))
		{
			$query = call_user_func([ $arUserField['USER_TYPE']['CLASS_NAME'], 'getList' ], $arUserField);
			$enum = $query ? static::toArray($query) : [];
			$enumMap = array_column($enum, 'VALUE', 'ID');
			$displayValues = [];

			foreach ((array)$arHtmlControl['VALUE'] as $value)
			{
				if (isset($enumMap[$value]))
				{
					$displayValues[] = $enumMap[$value];
				}
				else
				{
					$displayValues[] = sprintf('[%s]', $value);
				}
			}

			$result = implode(' / ', $displayValues);
			$result = static::htmlEscape($result);
		}

		return $result;
	}

	public static function toArray($enum)
	{
		if (is_array($enum))
		{
			$result = $enum;

			foreach ($result as &$option)
			{
				foreach ($option as $key => $value)
				{
					$option[$key] = htmlspecialcharsbx($value, ENT_COMPAT, false);
				}
			}
			unset($option);
		}
		else if ($enum instanceof \CDBResult)
		{
			$result = [];

			while ($option = $enum->GetNext())
			{
				$result[] = $option;
			}
		}
		else
		{
			$result = [];
		}

		return $result;
	}

	public static function htmlEscape($string)
	{
		static $search = [ '"', '<', '>' ];
		static $replace = [ '&quot;', '&lt;', '&gt;' ];

		return str_replace($search, $replace, $string);
	}

	protected static function getValueMap($value)
	{
		if (is_array($value))
		{
			$result = array_flip($value);
		}
		else if ((string)$value !== '')
		{
			$result = [ $value => true ];
		}
		else
		{
			$result = [];
		}

		return $result;
	}

	protected static function makeSelectViewSettings($userField) : array
	{
		$settings = (array)$userField['SETTINGS'];

		if (!isset($settings['ALLOW_NO_VALUE']) && $userField['MANDATORY'] !== 'Y')
		{
			$settings['ALLOW_NO_VALUE'] = 'Y';
		}

		return $settings;
	}
}