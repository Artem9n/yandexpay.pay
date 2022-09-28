<?php
namespace YandexPay\Pay\Logger;

use Bitrix\Main;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference;

class Cleaner extends Reference\Event\Regular
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
		$delete = [];

		$query = Table::getList([
			'filter' => [
				'<=TIMESTAMP_X' => $dateTime,
			],
			'select' => [ 'ID' ],
		]);

		while ($row = $query->fetch())
		{
			$delete[] = $row['ID'];
		}

		if (empty($delete)) { return; }

		foreach ($delete as $id)
		{
			Table::delete($id);
		}
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