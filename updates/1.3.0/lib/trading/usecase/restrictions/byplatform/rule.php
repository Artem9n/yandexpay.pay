<?php

namespace YandexPay\Pay\Trading\UseCase\Restrictions\ByPlatform;

use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Trading\Entity\Sale\Platform;

Loc::loadMessages(__FILE__);

class Rule
{
	protected static $platformId = null;

	public static function getClassTitle() : string
	{
		return Loc::getMessage('YANDEX_PAY_TRADING_USE_CASE_RESTRICTION_BY_PLATFORM');
	}

	public static function getClassDescription() : string
	{
		return '';
	}

	public static function isAvailable() : bool
	{
		$platformId = static::getPlatformId();

		return $platformId !== null;
	}

	public static function getParamsStructure() : array
	{
		return [
			'VIEW' => [
				'TYPE' => 'ENUM',
				'LABEL' => Loc::getMessage('YANDEX_PAY_TRADING_USE_CASE_RESTRICTION_BY_PLATFORM_PARAM_VIEW'),
				'OPTIONS' => [
					'YANDEX_CHECKOUT' => Loc::getMessage('YANDEX_PAY_TRADING_USE_CASE_RESTRICTION_BY_PLATFORM_ONLY_YANDEX_CHECKOUT'),
					'SITE' => Loc::getMessage('YANDEX_PAY_TRADING_USE_CASE_RESTRICTION_BY_PLATFORM_ONLY_SITE'),
				]
			],
		];
	}

	public static function check(array $params, array $config) : bool
	{
		$result = in_array(static::getPlatformId(), $params, true);

		if ($config['VIEW'] === 'SITE')
		{
			$result = !$result;
		}

		return $result;
	}

	public static function extractParams(Sale\Order $order) : array
	{
		$tradingCollection = $order->getTradeBindingCollection();
		return static::extractParamsFromTradingCollection($tradingCollection);
	}

	protected static function extractParamsFromTradingCollection(Sale\TradeBindingCollection $collection) : array
	{
		$result = [];

		/** @var Sale\TradeBindingEntity $trading */
		foreach ($collection as $trading)
		{
			$platformId = (int)$trading->getField('TRADING_PLATFORM_ID');

			if ($platformId <= 0) { continue; }

			$result[] = $platformId;
		}

		return $result;
	}

	protected static function getPlatformId() : ?int
	{
		if (!isset(static::$platformId))
		{
			static::$platformId = static::loadPlatformId();
		}

		return static::$platformId;
	}

	protected static function loadPlatformId() : ?int
	{
		$platform = Sale\TradingPlatformTable::getList([
			'filter' => [
				'=CODE' => Platform::TRADING_PLATFORM_CODE
			],
			'limit' => 1,
		])->fetch();

		return $platform['ID'] ?? null;
	}
}