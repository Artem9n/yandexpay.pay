<?php

namespace YandexPay\Pay\Reference\Concerns;

use YandexPay\Pay\Config;
use YandexPay\Pay\Utils\MessageRegistry;

trait HasMessage
{
	private static function getMessagePrefix() : string
	{
		return MessageRegistry::getModuleInstance()->getPrefix(self::class);
	}

	private static function includeSelfMessages(): void
	{
		MessageRegistry::getModuleInstance()->load(self::class);
	}

	protected static function getMessage($code, $replaces = null, $fallback = null) : ?string
	{
		self::includeSelfMessages();

		$fullCode = self::getMessagePrefix() . '_' . $code;

		if ($fallback === null) { $fallback = $code; }

		return Config::getLang($fullCode, $replaces, $fallback);
	}
}