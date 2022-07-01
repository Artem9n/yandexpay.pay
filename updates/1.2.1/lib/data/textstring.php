<?php

namespace YandexPay\Pay\Data;

use Bitrix\Main;

class TextString
{
	public static function match($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
	{
		$needConvert = !Main\Application::isUtfMode() && static::hasPatternUnicode($pattern);

		if ($needConvert)
		{
			$subject = Main\Text\Encoding::convertEncoding($subject, LANG_CHARSET, 'UTF-8');
		}

		$result = preg_match($pattern, $subject, $matches, $flags, $offset);

		if ($result && $needConvert)
		{
			$matches = Main\Text\Encoding::convertEncoding($matches, 'UTF-8', LANG_CHARSET);
		}

		return $result;
	}

	protected static function hasPatternUnicode(string $pattern) : bool
	{
		$wrapSymbol = mb_substr($pattern, 0, 1);
		$wrapClosePosition = mb_strrpos($pattern, $wrapSymbol, 1);
		$result = false;

		if ($wrapClosePosition !== false)
		{
			$modifiers = mb_substr($pattern, $wrapClosePosition);
			$result = (mb_strpos($modifiers, 'u') !== false);
		}

		return $result;
	}
}