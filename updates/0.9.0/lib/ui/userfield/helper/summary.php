<?php

namespace YandexPay\Pay\Ui\UserField\Helper;

use YandexPay\Pay;

class Summary
{
	public static function make(array $fields, array $values, string $template = '') : string
	{
		if ($template !== '')
		{
			$fields = SummaryTemplate::normalizeNames($fields);
			$fieldKeys = SummaryTemplate::getUsedKeys($template);
			$usedFields = array_intersect_key($fields, array_flip($fieldKeys));
			$displayValues = static::getDisplayValues($usedFields, $values);

			$result = SummaryTemplate::render($template, $displayValues);
		}
		else
		{
			$usedFields = static::getFieldsWithSummary($fields);
			$displayValues = static::getDisplayValues($usedFields, $values);

			$result = implode(', ', $displayValues);
		}

		return trim($result);
	}

	protected static function getFieldsWithSummary(array $fields) : array
	{
		$result = [];

		foreach ($fields as $fieldKey => $field)
		{
			if (!empty($field['SETTINGS']['SUMMARY']))
			{
				$result[$fieldKey] = $field;
			}
		}

		if (empty($result) && !empty($fields))
		{
			$field = reset($fields);
			$fieldKey = key($fields);

			$result[$fieldKey] = $field;
		}

		return $result;
	}

	protected static function getDisplayValues(array $fields, array $values) : array
	{
		$result = [];

		foreach ($fields as $key => $field)
		{
			$fieldValue = Pay\Utils\DotChain::get($values, $key);

			if ($fieldValue === null) { continue; }
			if (!empty($field['HIDDEN']) && $field['HIDDEN'] !== 'N') { continue; }
			if (isset($field['DEPEND']) && !Pay\Utils\UserField\DependField::test($field['DEPEND'], $values)) { continue; }

			$hasSummaryTemplate = !empty($field['SETTINGS']['SUMMARY']) && is_string($field['SETTINGS']['SUMMARY']);
			$isMultiple = (isset($field['MULTIPLE']) && $field['MULTIPLE'] !== 'N');

			if ($hasSummaryTemplate && $isMultiple)
			{
				$parts = [];

				if (is_array($fieldValue))
				{
					foreach ($fieldValue as $fieldValueItem)
					{
						$parts[] = static::getDisplayValue($field, $fieldValueItem, $values);
					}
				}

				$result[$key] = implode(', ', $parts);
			}
			else
			{
				$result[$key] = static::getDisplayValue($field, $fieldValue, $values);
			}
		}

		return $result;
	}

	protected static function getDisplayValue(array $field, $value, array $values = null) : ?string
	{
		$valueFormatted = static::formatValue($field, $value, $values);
		$unit = static::formatUnit($field, $valueFormatted);

		return ($unit !== '')
			? $valueFormatted . '&nbsp;' . $unit
			: $valueFormatted;
	}

	protected static function formatValue(array $field, $value, array $values = null) : ?string
	{
		if (!empty($field['SETTINGS']['SUMMARY']) && is_string($field['SETTINGS']['SUMMARY']))
		{
			$template = $field['SETTINGS']['SUMMARY'];
			$templateKeys = SummaryTemplate::getUsedKeys($template);
			$templateVars = is_array($value) ? $value : [];

			if (isset($field['FIELDS']))
			{
				$templateVars = static::getDisplayValues($field['FIELDS'], $templateVars);
			}

			if (!empty($templateKeys) && in_array('VALUE', $templateKeys, true))
			{
				$templateVars['VALUE'] = static::getSystemDisplayValue($field, $value, $values);
			}

			$displayValue = SummaryTemplate::render($template, $templateVars);
		}
		else
		{
			$displayValue = static::getSystemDisplayValue($field, $value, $values);
		}

		return $displayValue;
	}

	protected static function getSystemDisplayValue(array $field, $value, array $values) : ?string
	{
		$field = Field::extend($field);
		$result = Renderer::getViewHtml($field, $value, $values);

		if ($result === '&nbsp;')
		{
			$result = null;
		}

		return $result;
	}

	protected static function formatUnit(array $field, $value) : string
	{
		$number = static::getValueNumber($value);

		if ($number === null || empty($field['SETTINGS']['UNIT']))
		{
			$result = '';
		}
		else if (is_array($field['SETTINGS']['UNIT']))
		{
			$result = Pay\Utils\Morph::decline($number, $field['SETTINGS']['UNIT']);
		}
		else
		{
			$result = (string)$field['SETTINGS']['UNIT'];
		}

		return $result;
	}

	protected static function getValueNumber($value) : ?int
	{
		$result = null;

		if (is_numeric($value))
		{
			$result = (int)$value;
		}
		else if (preg_match('/(\d+([.,]\d+)?)\D*$/', $value, $numberMatch))
		{
			$result = (int)$numberMatch[1];
		}

		return $result;
	}
}