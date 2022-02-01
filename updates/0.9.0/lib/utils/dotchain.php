<?php

namespace YandexPay\Pay\Utils;

class DotChain
{
	public static function get(array $values, $key)
	{
		$keyParts = static::splitKey($key);
		$lastLevel = $values;

		foreach ($keyParts as $keyPart)
		{
			if (isset($lastLevel[$keyPart]))
			{
				$lastLevel = $lastLevel[$keyPart];
			}
			else
			{
				$lastLevel = null;
				break;
			}
		}

		return $lastLevel;
	}

	public static function set(array &$values, $key, $value) : void
	{
		$keyParts = static::splitKey($key);
		$keyPartIndex = 0;
		$keyPartCount = count($keyParts);
		$lastLevel = &$values;

		foreach ($keyParts as $keyPart)
		{
			if ($keyPartCount === $keyPartIndex + 1)
			{
				$lastLevel[$keyPart] = $value;
			}
			else
			{
				if (!isset($lastLevel[$keyPart]) || !is_array($lastLevel[$keyPart]))
				{
					$lastLevel[$keyPart] = [];
				}

				$lastLevel = &$lastLevel[$keyPart];
			}

			$keyPartIndex++;
		}
	}

	public static function push(array &$values, $key, $value) : void
	{
		$keyParts = static::splitKey($key);
		$keyPartIndex = 0;
		$keyPartCount = count($keyParts);
		$lastLevel = &$values;

		foreach ($keyParts as $keyPart)
		{
			if ($keyPartCount === $keyPartIndex + 1)
			{
				if (!isset($lastLevel[$keyPart]))
				{
					$lastLevel[$keyPart] = [];
				}

				$lastLevel[$keyPart][] = $value;
			}
			else
			{
				if (!isset($lastLevel[$keyPart]) || !is_array($lastLevel[$keyPart]))
				{
					$lastLevel[$keyPart] = [];
				}

				$lastLevel = &$lastLevel[$keyPart];
			}

			$keyPartIndex++;
		}
	}

	public static function unset(array &$values, $key) : void
	{
		$keyParts = static::splitKey($key);
		$keyPartIndex = 0;
		$keyPartCount = count($keyParts);
		$lastLevel = &$values;

		foreach ($keyParts as $keyPart)
		{
			if (!isset($lastLevel[$keyPart])) { break; }

			if ($keyPartCount === $keyPartIndex + 1)
			{
				unset($lastLevel[$keyPart]);
			}
			else
			{
				$lastLevel = &$lastLevel[$keyPart];
			}

			$keyPartIndex++;
		}
	}

	public static function splitKey($key) : array
	{
		return explode('.', $key);
	}

	public static function joinKey(array $chain) : string
	{
		return implode('.', $chain);
	}
}