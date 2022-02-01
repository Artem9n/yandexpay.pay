<?php
namespace YandexPay\Pay\Reference\Storage\Field;

use Bitrix\Main;

class FuzzyField extends Main\ORM\Fields\ArrayField
{
	private const ENCODE_PREFIX = '__:';

	public function encode($value)
	{
		if ($value !== null && !is_scalar($value))
		{
			$value = self::ENCODE_PREFIX . parent::encode($value);
		}

		return (string)$value;
	}

	public function decode($value)
	{
		if (mb_strpos($value, self::ENCODE_PREFIX) === 0)
		{
			$value = mb_substr($value, mb_strlen(self::ENCODE_PREFIX));
			$value = parent::decode($value);
		}

		return $value;
	}

	public function cast($value)
	{
		return $value;
	}

	public function getGetterTypeHint()
	{
		return 'mixed';
	}

	public function getSetterTypeHint()
	{
		return 'mixed';
	}
}