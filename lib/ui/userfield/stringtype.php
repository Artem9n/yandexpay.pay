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
		return array_diff_key(static::callParent('getUserTypeDescription'), [
			'USE_FIELD_COMPONENT' => true,
		]);
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

	public static function GetEditFormHtmlMulty($userField, $htmlControl)
	{
		$values = Helper\Value::asMultiple($userField, $htmlControl);
		$tableAttributes = Fieldset\Helper::makeChildAttributes($userField);
		$inputAttributes = Helper\Attributes::extractFromSettings($userField['SETTINGS']);
		$valueIndex = 0;
		$minCount = isset($userField['SETTINGS']['MIN_COUNT']) ? max(1, (int)$userField['SETTINGS']['MIN_COUNT']) : 1;
		$inputCount = max(count($values), $minCount);

		$result = sprintf('<table %s>', Helper\Attributes::stringify($tableAttributes));

		for ($i = 0; $i < $inputCount; $i++)
		{
			$value = $values[$i] ?? null;

			$result .= '<tr><td>';
			$result .= static::getEditInput($userField, [
				'NAME' => $userField['FIELD_NAME'] . '[' . $valueIndex . ']',
				'VALUE' => $value,
			], $inputAttributes);
			$result .= '</td></tr>';

			++$valueIndex;
		}

		$result .= '</table>';

		return $result;
	}

	public static function getEditFormHTML($userField, $htmlControl) : string
	{
		$htmlControl['VALUE'] = Helper\Value::asSingle($userField, $htmlControl);
		$attributes = Helper\Attributes::extractFromSettings($userField['SETTINGS']);

		return static::getEditInput($userField, $htmlControl, $attributes);
	}

	public static function GetAdminListViewHtml($userField, $htmlControl) : string
	{
		$value = (string)Helper\Value::asSingle($userField, $htmlControl);

		return $value !== '' ? $value : '&nbsp;';
	}

	protected static function getEditInput($userField, $htmlControl, array $attributes = []) : string
	{
		if ($userField['SETTINGS']['ROWS'] < 2)
		{
			$htmlControl['VALIGN'] = 'middle';
			$attributes += [
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

		$attributes += [
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