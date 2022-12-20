<?php
namespace YandexPay\Pay\Logger;

use Bitrix\Main;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference;

class Cleaner extends Reference\Agent\Regular
{
	public static function getDefaultParams() : array
	{
		return [
			'interval' => 86400,
		];
	}

	public static function run() : void
	{
		$days = static::getExpireDays();

		if ($days > 0)
		{
			$date = static::buildExpireDate($days);
			static::cleanTable($date);
		}
	}

	protected static function cleanTable(Main\Type\DateTime $dateTime) : void
	{
		$batch = new Reference\Storage\Facade\DeleteBatch(Table::class);

		$batch->run([
			'filter' => [ '<=TIMESTAMP_X' => $dateTime ],
		]);
	}

	protected static function getExpireDays() : int
	{
		return (int)Config::getOption('log_expire_days', 10);
	}

	protected static function buildExpireDate(int $days) : Main\Type\DateTime
	{
		$result = new Main\Type\DateTime();
		$result->add('-P' . $days . 'D');

		return $result;
	}
}