<?php

namespace YandexPay\Pay\Data;

class Phone
{
	public const FORMAT_INTERNATIONAL_FORMATTED = 'internationalFormatted';
	public const FORMAT_REGIONAL_FORMATTED = 'regionalFormatted';
	public const FORMAT_INTERNATIONAL_NUMERIC = 'internationalNumeric';
	public const FORMAT_REGIONAL_NUMERIC = 'regionalNumeric';
	public const FORMAT_CUSTOM = 'custom';

	protected static $formatMasks = [
		self::FORMAT_INTERNATIONAL_FORMATTED => '+7 495 000-00-00',
		self::FORMAT_REGIONAL_FORMATTED => '8 495 000-00-00',
		self::FORMAT_INTERNATIONAL_NUMERIC => '+74950000000',
		self::FORMAT_REGIONAL_NUMERIC => '84950000000',
	];

	public static function format(string $value, string $format = null) : string
	{
		if ($format === null) { $format = self::FORMAT_INTERNATIONAL_NUMERIC; }

		if ($format !== null)
		{
			$mask = static::getMask($format);
			$result = static::applyMask($value, $mask);
		}
		else
		{
			$result = $value;
		}

		return $result;
	}

	public static function getFormatVariants() : array
	{
		return [
			static::FORMAT_INTERNATIONAL_FORMATTED,
			static::FORMAT_REGIONAL_FORMATTED,
			static::FORMAT_INTERNATIONAL_NUMERIC,
			static::FORMAT_REGIONAL_NUMERIC,
		];
	}

	public static function getMask(string $format) : string
	{
		return static::$formatMasks[$format];
	}

	protected static function applyMask($value, $mask) : string
	{
		$maskLength = mb_strlen($mask);
		$valueSanitized = static::sanitize($value);
		$valueIndex = 0;
		$valueLength = mb_strlen($valueSanitized);
		$result = '';

		for ($maskIndex = 0; $maskIndex < $maskLength; $maskIndex++)
		{
			if ($valueIndex >= $valueLength) { break; }

			$maskSymbol = mb_substr($mask, $maskIndex, 1);

			if (!is_numeric($maskSymbol))
			{
				$result .= $maskSymbol;
			}
			else if ($valueIndex < $valueLength)
			{
				$valueSymbol = mb_substr($valueSanitized, $valueIndex, 1);

				if ($valueIndex === 0)
				{
					$valueSymbol = static::resolveFirstSymbolCollision($valueSymbol, $maskSymbol, $value, $mask);
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