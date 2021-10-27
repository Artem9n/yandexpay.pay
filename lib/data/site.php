<?php

namespace YandexPay\Pay\Data;

use Bitrix\Main;

class Site
{
	protected static $enum;
	protected static $urlVariables;

	public static function getVariants() : array
	{
		$enum = static::getEnum();

		return array_column($enum, 'ID');
	}

	public static function getOptions() : array
	{
		return static::getEnum();
	}

	public static function getTitle($siteId) : string
	{
		$siteId = (string)$siteId;
		$result = null;

		foreach (static::getEnum() as $option)
		{
			if ($option['ID'] === $siteId)
			{
				$result = $option['NAME'];
				break;
			}
		}

		return $result;
	}

	public static function getDefault()
	{
		$enum = static::getEnum();
		$option = reset($enum);

		return $option !== false ? $option['ID'] : null;
	}

	protected static function getEnum() : array
	{
		if (static::$enum === null)
		{
			static::$enum = static::loadEnum();
		}

		return static::$enum;
	}

	protected static function loadEnum() : array
	{
		$result = [];

		$query = Main\SiteTable::getList([
			'filter' => [ '=ACTIVE' => 'Y' ],
			'order' => [ 'DEF' => 'DESC', 'SORT' => 'ASC' ],
			'select' => [ 'LID', 'NAME' ]
		]);

		while ($row = $query->fetch())
		{
			$result[] = [
				'ID' => (string)$row['LID'],
				'VALUE' => '[' . $row['LID'] . '] ' . $row['NAME'],
			];
		}

		return $result;
	}

	public static function getUrlVariables($siteId)
	{
		if (!isset(static::$urlVariables[$siteId]))
		{
			static::$urlVariables[$siteId] = static::loadUrlVariables($siteId);
		}

		return static::$urlVariables[$siteId];
	}

	protected static function loadUrlVariables($siteId)
	{
		$result = false;

		$query = Main\SiteTable::getList([
			'filter' => [ '=LID' => $siteId ],
			'select' => [ 'SERVER_NAME', 'DIR' ]
		]);

		if ($site = $query->fetch())
		{
			$result = [
				'from' => [ '#SITE_DIR#', '#SERVER_NAME#', '#LANG#', '#SITE#' ],
				'to' => [ $site['DIR'], $site['SERVER_NAME'], $site['DIR'], $siteId ]
			];
		}

		return $result;
	}
}
