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

	public static function getTemplate(string $siteId = null) : array
	{
		$result = [];

		$filter = [];

		if ($siteId !== null)
		{
			$filter = ['=SITE_ID' => $siteId];
		}

		$query = Main\SiteTemplateTable::getList([
			'filter' => $filter,
			'select' => [
				'TEMPLATE'
			]
		]);

		while($site = $query->fetch())
		{
			$result[] = $site['TEMPLATE'];
		}

		return $result;
	}

	public static function getDefault()
	{
		$enum = static::getEnum();
		$option = reset($enum);

		return $option !== false ? $option['ID'] : null;
	}

	public static function getDir(string $siteId)
	{
		$result = null;

		$query = Main\SiteTable::getList([
			'filter' => [ '=LID' => $siteId ],
			'limit' => 1,
			'select' => [ 'LID', 'NAME', 'DIR' ]
		]);

		if ($row = $query->fetch())
		{
			$result = $row['DIR'];
		}

		return $result;
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
			if (static::isCrm($row['LID'])) { continue; }

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

	public static function isCrm($siteId)
	{
		if (defined('BX24_HOST_NAME'))
		{
			$result = ($siteId === SiteDomain::getSite(BX24_HOST_NAME));
		}
		else
		{
			$result = static::hasCrmTemplate($siteId);
		}

		return $result;
	}

	protected static function hasCrmTemplate($siteId)
	{
		$query = Main\SiteTemplateTable::getList([
			'filter' => [
				'=SITE_ID' => $siteId,
				'%TEMPLATE' => 'bitrix24',
			],
			'limit' => 1,
		]);

		return (bool)$query->fetch();
	}
}
