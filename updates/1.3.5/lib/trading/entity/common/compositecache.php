<?php

namespace YandexPay\Pay\Trading\Entity\Common;

use Bitrix\Main;
use YandexPay\Pay\Trading\Entity as TradingEntity;

class CompositeCache extends TradingEntity\Reference\CompositeCache
{
	public function clearCache(string $domain) : void
	{
		if (
			!Main\Composite\Helper::isCompositeEnabled()
			|| !in_array($domain, Main\Composite\Helper::getDomains(), true)
		)
		{ return; }

		TradingEntity\Common\Composite\Clear::register([
			'method' => 'clear',
			'arguments' => [
				$domain
			]
		]);
	}
}