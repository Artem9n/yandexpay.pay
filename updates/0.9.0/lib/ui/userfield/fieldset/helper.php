<?php

namespace YandexPay\Pay\Ui\UserField\Fieldset;

class Helper
{
	public static function makeChildAttributes(array $userField, string $type = 'row__child') : array
	{
		$attributes = [];

		if (static::hasParentFieldset($userField))
		{
			$attributes['class'] = static::getParentFieldsetName($userField, $type);
			$attributes['data-name'] = static::makeRelativeName($userField, $userField['FIELD_NAME']);
		}

		return $attributes;
	}

	public static function hasParentFieldset(array $userField) : bool
	{
		return !empty($userField['SETTINGS']['PARENT_FIELDSET_BASE']);
	}

	public static function getParentFieldsetName(array $userField, string $type) : string
	{
		$parentName = $userField['SETTINGS']['PARENT_FIELDSET_BASE'];

		return $parentName . '-' . $type;
	}

	public static function makeRelativeName(array $userField, string $inputName) : string
	{
		if (empty($userField['SETTINGS']['PARENT_FIELDSET_NAME'])) { return $inputName; }

		$parentName = $userField['SETTINGS']['PARENT_FIELDSET_NAME'];
		$parentName = preg_replace('/\[]$/', '', $parentName);

		if (mb_strpos($inputName, $parentName) === 0)
		{
			$result = mb_substr(
				$inputName,
				mb_strlen($parentName)
			);
			$result = preg_replace('/^\[\d+]/', '', $result); // remove collection index

			if (preg_match('/^\[([^]]+)]$/', $result, $simplifyMatches)) // remove root quotes
			{
				$result = $simplifyMatches[1];
			}
		}
		else
		{
			$result = $inputName;
		}

		return $result;
	}
}