<?php

namespace YandexPay\Pay\Admin;

use YandexPay\Pay;

class Library
{
	public static function resolve(string $name, array $fallback = []) : string
	{
		$option = (string)Pay\Config::getOption('library_' . $name, '');
		$variants = array_merge(
			[ $name ],
			$fallback
		);

		if ($option !== '' && in_array($option, $variants, true)) { return $option; }

		$result = $name;

		foreach ($variants as $variant)
		{
			if (\CJSCore::isExtensionLoaded($variant))
			{
				$result = $variant;
				break;
			}
		}

		return $result;
	}
}
