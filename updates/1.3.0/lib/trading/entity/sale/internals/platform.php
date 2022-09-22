<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Internals;

use Bitrix\Sale;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Reference\Concerns;

class Platform extends Sale\TradingPlatform\Platform
{
	use Concerns\HasMessage;

	public function install()
	{
		$fields = [
			'CODE' => $this->getCode(),
			'CLASS' => '\\' . static::class,
			'ACTIVE' => 'Y',
			'NAME' => self::getMessage('NAME'),
			'SETTINGS' => '',
		];

		$result = Sale\TradingPlatformTable::add($fields);
		Exceptions\Facade::handleResult($result);

		self::$instances[$this->getCode()] = new static($this->getCode());

		return $result->getId();
	}
}