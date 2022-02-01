<?php

namespace YandexPay\Pay\Reference\Concerns;

use Bitrix\Main\Localization\Loc;
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

		$fullCode = Config::getLangPrefix() . self::getMessagePrefix() . '_' . $code;

		$result = Loc::getMessage($fullCode, $replaces);

		if ($result === '' || $result === null)
		{
			$result = $fallback ?? $code;
		}

		return $result;
	}
}