<?php
namespace YandexPay\Pay\Ui\SaleInput;

use Bitrix\Sale;

class Registry
{
	public const PREFIX = 'yapay';
	public const TYPE_MERCHANT = 'merchant';

	protected static $ready = false;

	public static function type(string $name) : string
	{
		return static::PREFIX . ucfirst($name) . 'Type';
	}

	public static function register() : void
	{
		if (static::$ready) { return; }

		static::$ready = true;

		foreach (static::available() as $name => $class)
		{
			$type = static::type($name);

			Sale\Internals\Input\Manager::register($type, [
				'CLASS' => $class,
			]);
		}
	}

	protected static function available() : array
	{
		return [
			static::TYPE_MERCHANT => MerchantType::class,
		];
	}
}