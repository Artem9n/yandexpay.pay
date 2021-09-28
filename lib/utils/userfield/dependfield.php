<?php
namespace YandexPay\Pay\Utils\Userfield;

class DependField
{
	public const RULE_ANY = 'ANY';
	public const RULE_EXCLUDE = 'EXCLUDE';
	public const RULE_EMPTY = 'EMPTY';

	public static function test(array $rules, array $values) : bool
	{
		$result = true;

		foreach ($rules as $fieldName => $rule)
		{
			$value = $values[$fieldName] ?? null;

			switch ($rule['RULE'])
			{
				case static::RULE_EMPTY:
					$isDependValueEmpty = static::isEmpty($value);
					$isMatch = ($isDependValueEmpty === $rule['VALUE']);
				break;

				case static::RULE_ANY:
					$isMatch = static::applyRuleAny($rule['VALUE'], $value);
				break;

				case static::RULE_EXCLUDE:
					$isMatch = !static::applyRuleAny($rule['VALUE'], $value);
				break;

				default:
					$isMatch = true;
				break;
			}

			if (!$isMatch)
			{
				$result = false;
				break;
			}
		}

		return $result;
	}

	protected static function isEmpty($value) : bool
	{
		if (is_scalar($value))
		{
			$result = (string)$value === '' || (string)$value === '0';
		}
		else
		{
			$result = empty($value);
		}

		return $result;
	}

	protected static function applyRuleAny($ruleValue, $formValue) : bool
	{
		$isRuleMultiple = is_array($ruleValue);
		$isFormMultiple = is_array($formValue);

		if ($isFormMultiple && $isRuleMultiple)
		{
			$intersect = array_intersect($ruleValue, $formValue);
			$result = !empty($intersect);
		}
		else if ($isFormMultiple)
		{
			/** @noinspection TypeUnsafeArraySearchInspection */
			$result = in_array($ruleValue, $formValue);
		}
		else if ($isRuleMultiple)
		{
			/** @noinspection TypeUnsafeArraySearchInspection */
			$result = in_array($formValue, $ruleValue);
		}
		else
		{
			/** @noinspection TypeUnsafeComparisonInspection */
			$result = ($formValue == $ruleValue);
		}

		return $result;
	}
}