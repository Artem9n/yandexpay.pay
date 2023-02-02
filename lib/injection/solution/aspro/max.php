<?php
namespace YandexPay\Pay\Injection\Solution\Aspro;

use Bitrix\Main;
use YandexPay\Pay\Injection\Solution;
use YandexPay\Pay\Reference\Concerns;

class Max extends Base
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function getType() : string
	{
		return Solution\Registry::ASPRO_MAX;
	}

	public function isMatch(array $context = []) : bool
	{
		$result = false;

		if (Main\ModuleManager::isModuleInstalled('aspro.max'))
		{
			static::$isMatch = true;
			$result = true;
		}

		return $result;
	}
}