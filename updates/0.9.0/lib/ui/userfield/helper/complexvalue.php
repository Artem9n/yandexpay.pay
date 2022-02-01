<?php

namespace YandexPay\Pay\Ui\UserField\Helper;

class ComplexValue extends Value
{
	protected static function isSingle($value) : bool
	{
		return is_array($value) && static::isAssociativeArray($value);
	}

	protected static function isEmpty($value) : bool
	{
		return !is_array($value) || empty($value);
	}

	protected static function isMultiple($value) : bool
	{
		return is_array($value) && !static::isAssociativeArray($value);
	}

	protected static function isAssociativeArray($value) : bool
	{
		$result = false;

		foreach ($value as $key => $item)
		{
			if (!is_numeric($key))
			{
				$result = true;
				break;
			}
		}

		return $result;
	}
}