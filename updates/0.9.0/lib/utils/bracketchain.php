<?php

namespace YandexPay\Pay\Utils;

class BracketChain extends DotChain
{
	public static function splitKey($key) : array
	{
		if (is_array($key)) { return $key; }

		$keyOffset = 0;
		$keyLength = mb_strlen($key);
		$keyChain = [];

		do
		{
			if ($keyOffset === 0)
			{
				$arrayEnd = mb_strpos($key, '[');

				if ($arrayEnd === false)
				{
					$keyPart = $key;
					$keyOffset = $keyLength;
				}
				else
				{
					$keyPart = mb_substr($key, $keyOffset, $arrayEnd - $keyOffset);
					$keyOffset = $arrayEnd + 1;
				}
			}
			else
			{
				$arrayEnd = mb_strpos($key, ']', $keyOffset);

				if ($arrayEnd === false)
				{
					$keyPart = mb_substr($key, $keyOffset);
					$keyOffset = $keyLength;
				}
				else
				{
					$keyPart = mb_substr($key, $keyOffset, $arrayEnd - $keyOffset);
					$keyOffset = $arrayEnd + 2;
				}
			}

			if ($keyPart !== '')
			{
				$keyChain[] = $keyPart;
			}
			else
			{
				break;
			}
		}
		while ($keyOffset < $keyLength);

		return $keyChain;
	}

	public static function joinKey(array $chain) : string
	{
		$result = array_shift($chain);

		if (!empty($chain))
		{
			$result .= '[' . implode('][', $chain) . ']';
		}

		return $result;
	}
}