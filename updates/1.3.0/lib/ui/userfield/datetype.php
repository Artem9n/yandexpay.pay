<?php

namespace YandexPay\Pay\Ui\UserField;

use Bitrix\Main;

class DateType
{
	use Concerns\HasCompatibleExtends;

	public static function getCommonExtends() : string
	{
		return Main\UserField\Types\DateType::class;
	}

	public static function getCompatibleExtends() : string
	{
		return \CUserTypeDate::class;
	}

	public static function getUserTypeDescription() : array
	{
		return array_diff_key(static::callParent('getUserTypeDescription'), [
			'USE_FIELD_COMPONENT' => true,
		]);
	}

	public static function checkFields($arUserField, $value)
	{
		return static::callParent('checkFields', [$arUserField, $value]);
	}

	public static function onBeforeSave($arUserField, $value)
	{
		return static::callParent('onBeforeSave', [$arUserField, $value]);
	}

	public static function getFilterHTML($arUserField, $arHtmlControl)
	{
		return static::callParent('getFilterHTML', [$arUserField, $arHtmlControl]);
	}

	public static function getFilterData($arUserField, $arHtmlControl)
	{
		return static::callParent('getFilterData', [$arUserField, $arHtmlControl]);
	}

	public static function getAdminListViewHTML($arUserField, $arHtmlControl)
	{
		return static::callParent('getAdminListViewHTML', [$arUserField, $arHtmlControl]);
	}

	public static function getEditFormHtml($userField, $htmlControl) : string
	{
		$value = Helper\Value::asSingle($userField, $htmlControl);

		$htmlControl['VALIGN'] = 'middle';

		return static::getEditInput($userField, [
			'NAME' => $htmlControl['NAME'],
			'VALUE' => $value,
		]);
	}

	public static function getEditFormHtmlMulty($userField, $htmlControl) : string
	{
		$values = Helper\Value::asMultiple($userField, $htmlControl);
		$attributes = Fieldset\Helper::makeChildAttributes($userField);
		$renderer = static function($name, $value) use ($userField) {
			return static::getEditInput($userField, [
				'NAME' => $name,
				'VALUE' => $value,
			]);
		};

		return View\Collection::render($userField['FIELD_NAME'], $values, $renderer, $attributes);
	}

	protected static function getEditInput($userField, $htmlControl) : string
	{
		\CJSCore::Init(['date']);
		Main\UI\Extension::load('yandexpaypay.admin.ui.input.calendarfield');

		$plugin = 'Ui.Input.CalendarField.Calendar';
		$size = isset($userField['SETTINGS']['SIZE']) ? (int)$userField['SETTINGS']['SIZE'] : 20;
		$rows = isset($userField['SETTINGS']['ROWS']) ? (int)$userField['SETTINGS']['ROWS'] : null;
		$format = $userField['SETTINGS']['FORMAT'] ?? null;
		$glue = $userField['SETTINGS']['GLUE'] ?? null;
		$readonly = true;

		if ($glue !== null)
		{
			$plugin = 'Ui.Input.CalendarField.CalendarGlue';
			$readonly = false;
		}

		$result = '<div class="adm-input-wrap adm-input-wrap-calendar">';

		if ($rows > 0)
		{
			$result .= sprintf('<textarea %s>%s</textarea>', Helper\Attributes::stringify([
				'class' => 'adm-input-calendar',
				'name' => $htmlControl['NAME'],
				'readonly' => $readonly,
				'cols' => $size + 3,
				'rows' => $rows,
			]), $htmlControl['VALUE']);
		}
		else
		{
			$result .= sprintf('<input %s />', Helper\Attributes::stringify([
				'class' => 'adm-input adm-input-calendar',
				'type' => 'text',
				'name' => $htmlControl['NAME'],
				'value' => $htmlControl['VALUE'],
				'readonly' => $readonly,
				'size' => $size + 3,
			]));
		}

		$result .= sprintf('<span %s></span>', Helper\Attributes::stringify(array_filter([
			'class' => 'adm-calendar-icon js-plugin',
			'title' => GetMessage("admin_lib_calend_title"),
			'data-plugin' => $plugin,
			'data-format' => $format,
			'data-glue' => $glue,
		])));
		$result .= '</div>';

		return $result;
	}
}