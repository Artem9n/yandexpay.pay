<?php

namespace YandexPay\Pay\Data;

class Phone
{
	public const MASK = '+74950000000';

	public static function format(string $value) : string
	{
		$maskLength = mb_strlen(static::MASK);
		$valueSanitized = static::sanitize($value);
		$valueIndex = 0;
		$valueLength = mb_strlen($valueSanitized);
		$result = '';

		for ($maskIndex = 0; $maskIndex < $maskLength; $maskIndex++)
		{
			if ($valueIndex >= $valueLength) { break; }

			$maskSymbol = mb_substr(static::MASK, $maskIndex, 1);

			if (!is_numeric($maskSymbol))
			{
				$result .= $maskSymbol;
			}
			else
			{
				$valueSymbol = mb_substr($valueSanitized, $valueIndex, 1);

				if ($valueIndex === 0)
				{
					$valueSymbol = static::resolveFirstSymbolCollision($valueSymbol, $maskSymbol, $value, static::MASK);
				}

				$result .= $valueSymbol;
				++$valueIndex;
			}
		}

		if ($valueIndex < $valueLength)
		{
			$result .= mb_substr($valueSanitized, $valueIndex);
		}

		return $result;
	}

	protected static function sanitize($value) : string
	{
		return preg_replace('/\D/', '', $value);
	}

	protected static function resolveFirstSymbolCollision($valueSymbol, $maskSymbol, $value, $mask)
	{
		$isValueInternational = static::isInternational($value);
		$isMaskInternational = static::isInternational($mask);
		$result = $valueSymbol;

		if ($isMaskInternational !== $isValueInternational)
		{
			if ($isMaskInternational && $maskSymbol === '7' && $valueSymbol === '8')
			{
				$result = $maskSymbol;
			}
			else if (!$isMaskInternational && $maskSymbol === '8' && $valueSymbol === '7')
			{
				$result = $maskSymbol;
			}
		}

		return $result;
	}

	protected static function isInternational($value) : bool
	{
		return mb_strpos($value, '+') === 0;
	}
}