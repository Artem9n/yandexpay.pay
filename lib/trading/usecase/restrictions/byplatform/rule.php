<?php

namespace YandexPay\Pay\Trading\UseCase\Restrictions\ByPlatform;

use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;

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

		return !empty($platformId);
	}

	public static function getParamsStructure() : array
	{
		return [
			'INVERT' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('YANDEX_PAY_TRADING_USE_CASE_RESTRICTION_BY_PLATFORM_PARAM_INVERT'),
			],
		];
	}

	public static function check($params, $config) : bool
	{
		$result = in_array(static::getPlatformId(), $params);

		if ($config['INVERT'] === 'Y')
		{
			$result = !$result;
		}

		return $result;
	}

	public static function extractParams(Sale\Order $order)
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
				'=CODE' => 'yapay_checkout'
			]
		])->fetch();

		return $platform['ID'] ?? null;
	}
}