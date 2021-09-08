<?php

namespace Yandexpay\Pay\Reference\Concerns;

use Yandexpay\Pay\Config;
use Yandexpay\Pay\Utils\MessageRegistry;

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