<?php

namespace YandexPay\Pay\Utils;

use Bitrix\Main;

class Encoding
{
	/**
	 * @param string|array $data
	 * @return string|array
	 */
	public static function convert($data)
	{
		$isUtf8Config = Main\Application::isUtfMode();

		if ($isUtf8Config) { return $data; }

		return Main\Text\Encoding::convertEncoding($data, LANG_CHARSET, 'UTF-8');
	}

	/**
	 * @param string|array $data
	 * @return string|array
	 */
	public static function revert($data)
	{
		$isUtf8Config = Main\Application::isUtfMode();

		if ($isUtf8Config) { return $data; }

		return Main\Text\Encoding::convertEncoding($data, 'UTF-8', LANG_CHARSET);
	}
}