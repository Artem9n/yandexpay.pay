<?php

namespace YandexPay\Pay\Utils;

use Bitrix\Main;

class Encoding
{
	public static function convert(string $data) : string
	{
		$isUtf8Config = Main\Application::isUtfMode();

		if ($isUtf8Config) { return $data; }

		return Main\Text\Encoding::convertEncoding($data, LANG_CHARSET, 'UTF-8');
	}

	public static function revert(string $data) : string
	{
		$isUtf8Config = Main\Application::isUtfMode();

		if ($isUtf8Config) { return $data; }

		return Main\Text\Encoding::convertEncoding($data, 'UTF-8', LANG_CHARSET);
	}
}