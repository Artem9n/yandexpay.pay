<?php
namespace YandexPay\Pay\Ui\UserField;

use YandexPay\Pay;

class FieldsetType
{
	public static function sanitizeFields(array $userField, $value) : ?array
	{
		if (!is_array($value)) { return null; }

		$result = [];

		foreach (static::getFields($userField) as $name => $field)
		{
			if (isset($field['DEPEND']) && !Pay\Utils\UserField\DependField::test($field['DEPEND'], $value)) { continue; }

			$fieldValue = Pay\Utils\BracketChain::get($value, $name);

			if ($field['MULTIPLE'] === 'Y')
			{
				$sanitizedValues = [];
				$fieldValue = is_array($fieldValue) ? $fieldValue : [];

				foreach ($fieldValue as $fieldValueItem)
				{
					$sanitizedValue = static::sanitizeUserFieldValue($field, $fieldValueItem);

					if (!Pay\Utils\Value::isEmpty($sanitizedValue))
					{
						$sanitizedValues[] = $fieldValueItem;
					}
				}

				if (!empty($sanitizedValues))
				{
					Pay\Utils\BracketChain::set($result, $name, $sanitizedValues);
				}
			}
			else
			{
				Pay\Utils\BracketChain::set($result, $name, $fieldValue);
			}
		}

		return $result;
	}

	protected static function sanitizeUserFieldValue(array $field, $value)
	{
		$result = $value;

		if (
			!empty($field['USER_TYPE']['CLASS_NAME'])
			&& is_callable([$field['USER_TYPE']['CLASS_NAME'], 'SanitizeFields'])
		)
		{
			$result = call_user_func(
				[$field['USER_TYPE']['CLASS_NAME'], 'SanitizeFields'],
				$field,
				$value
			);
		}

		return $result;
	}

	public static function GetAdminListViewHTML(array $userField, ?array $htmlControl) : string
	{
		$value = static::asSingle($userField, $htmlControl);

		return static::renderSummary($userField, $value);
	}

	public static function GetAdminListViewHTMLMulty(array $userField, ?array $htmlControl) : string
	{
		$parts = [];

		foreach (static::asMultiple($userField, $htmlControl) as $value)
		{
			$parts[] = static::renderSummary($userField, $value);
		}

		return implode(', ', $parts);
	}

	protected static function renderSummary(array $userField, $value) : string
	{
		$fields = static::getFields($userField);
		$summaryTemplate = $userField['SETTINGS']['SUMMARY'] ?? null;

		return !empty($value)
			? Helper\Summary::make($fields, $value, $summaryTemplate)
			: '';
	}

	public static function GetEditFormHtml(array $userField, ?array $htmlControl) : string
	{
		$values = static::asSingle($userField, $htmlControl);
		$layout = static::makeLayout($userField, $htmlControl);

		return $layout->edit($values);
	}

	public static function GetEditFormHtmlMulty(array $userField, ?array $htmlControl) : string
	{
		$values = static::asMultiple($userField, $htmlControl);
		$layout = static::makeLayout($userField, $htmlControl);

		return $layout->editMultiple($values);
	}

	protected static function asSingle(array $userField, ?array $htmlControl)
	{
		$result = Helper\ComplexValue::asSingle($userField, $htmlControl);
		$result = static::convertValue($userField, $result);

		return $result;
	}

	protected static function asMultiple(array $userField, ?array $htmlControl) : array
	{
		$result = [];

		foreach (Helper\ComplexValue::asMultiple($userField, $htmlControl) as $value)
		{
			$result[] = static::convertValue($userField, $value);
		}

		return $result;
	}

	protected static function convertValue(array $userField, $value)
	{
		return $value;
	}

	protected static function getFields(array $userField) : array
	{
		return (array)($userField['FIELDS'] ?? []);
	}

	protected static function makeLayout($userField, $htmlControl) : Fieldset\AbstractLayout
	{
		$fields = static::getFields($userField);
		$layout = !empty($userField['SETTINGS']['LAYOUT']) ? $userField['SETTINGS']['LAYOUT'] : 'table';

		if ($layout === 'summary')
		{
			$result = new Fieldset\SummaryLayout($userField, $htmlControl['NAME'], $fields);
		}
		else
		{
			$result = new Fieldset\TableLayout($userField, $htmlControl['NAME'], $fields);
		}

		return $result;
	}
}