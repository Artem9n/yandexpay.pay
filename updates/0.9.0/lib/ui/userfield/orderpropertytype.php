<?php
namespace YandexPay\Pay\Ui\UserField;

class OrderPropertyType extends EnumerationType
{
	public static function GetList($arUserField) : \CDBResult
	{
		$variants = $arUserField['VALUES'];
		$variants = static::filterVariants($arUserField, $variants);
		$variants = static::markDefaultVariant($arUserField, $variants);
		$variants = static::applyVariantsDefaultGroup($arUserField, $variants);

		$result = new \CDBResult();
		$result->InitFromArray($variants);

		return $result;
	}

	protected static function filterVariants($userField, $variants)
	{
		return static::filterDisabledTypes($userField, $variants);
	}

	protected static function filterDisabledTypes($userField, $variants)
	{
		$disabledTypes = static::getDisabledTypes($userField);
		$disabledMap = array_flip($disabledTypes);

		foreach ($variants as $variantKey => $variant)
		{
			if (isset($variant['TYPE'], $disabledMap[$variant['TYPE']]))
			{
				unset($variants[$variantKey]);
			}
		}

		return $variants;
	}

	protected static function getDisabledTypes($userField)
	{
		return isset($userField['SETTINGS']['DISABLED_TYPES'])
			? (array)$userField['SETTINGS']['DISABLED_TYPES']
			: [ 'LOCATION' ];
	}

	protected static function markDefaultVariant($userField, $variants)
	{
		if (isset($userField['SETTINGS']['TYPE']))
		{
			$type = $userField['SETTINGS']['TYPE'];
			$defaultKey = null;
			$methods = [
				'resolveDefaultVariantByType',
				'resolveDefaultVariantByCodeMatch',
				'resolveDefaultVariantByCodeSubstring',
				'resolveDefaultVariantBySimilarType',
			];

			foreach ($methods as $method)
			{
				$methodResult = static::$method($type, $variants);

				if ($methodResult !== null)
				{
					$defaultKey = $methodResult;
					break;
				}
			}

			if ($defaultKey !== null)
			{
				$variants[$defaultKey]['DEF'] = 'Y';
			}
		}

		return $variants;
	}

	protected static function resolveDefaultVariantByType($type, $variants)
	{
		$result = null;

		foreach ($variants as $variantKey => $variant)
		{
			if (isset($variant['TYPE']) && $variant['TYPE'] === $type)
			{
				$result = $variantKey;
				break;
			}
		}

		return $result;
	}

	protected static function resolveDefaultVariantByCodeMatch($type, $variants)
	{
		$result = null;

		foreach ($variants as $variantKey => $variant)
		{
			if (isset($variant['CODE']) && strcasecmp($variant['CODE'], $type) === 0)
			{
				$result = $variantKey;
				break;
			}
		}

		return $result;
	}

	protected static function resolveDefaultVariantByCodeSubstring($type, $variants)
	{
		$result = null;

		foreach ($variants as $variantKey => $variant)
		{
			if (isset($variant['CODE']) && mb_stripos($variant['CODE'], $type) !== false)
			{
				$result = $variantKey;
				break;
			}
		}

		return $result;
	}

	protected static function resolveDefaultVariantBySimilarType($type, $variants)
	{
		$similarTypes = static::getVariantSimilarTypes($type);
		$result = null;

		foreach ($similarTypes as $similarType)
		{
			$matchKey = static::resolveDefaultVariantByType($similarType, $variants);

			if ($matchKey !== null)
			{
				$result = $matchKey;
				break;
			}
		}

		return $result;
	}

	protected static function getVariantSimilarTypes($type)
	{
		switch ($type)
		{
			case 'LAST_NAME':
			case 'FIRST_NAME':
			case 'MIDDLE_NAME':
				$result = [ 'NAME' ];
			break;

			default:
				$result = [];
			break;
		}

		return $result;
	}

	protected static function applyVariantsDefaultGroup($userField, $variants)
	{
		if (empty($userField['SETTINGS']['DEFAULT_GROUP'])) { return $variants; }

		foreach ($variants as &$variant)
		{
			if (!empty($variant['GROUP'])) { continue; }

			$variant['GROUP'] = $userField['SETTINGS']['DEFAULT_GROUP'];
		}
		unset($variant);

		return $variants;
	}
}