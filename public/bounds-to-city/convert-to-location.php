<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main;
use Bitrix\Sale;


try
{
	// read -- from https://github.com/hflabs/city

	$input = require __DIR__ . '/read.php';

	if (empty($input)) { throw new Main\SystemException('empty city file'); }

	// map region

	$nameOverrides = [
		'Кукмор' => 'Кукмор посёлок городского типа',
		'Курчалой' => 'Курчалой село',
	];
	$known = [
		'саки' => '0001092143',
		'красноперекопск' => '0001086866',
	];
	$names =
		$nameOverrides
		+ array_column($input, 'region', 'region')
		+ array_column($input, 'area', 'area')
		+ array_column($input, 'city', 'city')
		+ array_column($input, 'settlement', 'settlement');
	$names = array_merge(
		array_values($names),
		array_map(
			static function($name) { return str_replace('ё', 'е', $name); },
			array_filter($names, static function($name) { return mb_stripos($name, 'ё') !== false; })
		)
	);
	$locationMap = [];

	$query = Sale\Location\LocationTable::getList([
		'filter' => [
			'=NAME.NAME' => $names,
			'=NAME.LANGUAGE_ID' => 'ru',
		],
		'select' => [
			'ID',
			'CODE',
			'NAME_NAME' => 'NAME.NAME',
		],
	]);

	while ($locationRow = $query->fetch())
	{
		$name = $locationRow['NAME_NAME'];
		$override = array_search($name, $nameOverrides, true);

		if ($override !== false)
		{
			$name = $override;
		}

		$name = mb_strtolower($name);
		$name = str_replace('ё', 'е', $name);

		if (!isset($locationMap[$name]))
		{
			$locationMap[$name] = [];
		}

		$locationMap[$name][] = $locationRow;
	}

	// resolve

	$outputMap = [
		'FOUND' => [],
		'FEW' => [],
		'NOT_FOUND' => [],
	];
	$regionOverrides = [
		'Саха /Якутия/' => 'Саха',
		'Севастополь' => 'Крым',
	];

	foreach ($input as $city)
	{
		$name = mb_strtolower($city['settlement'] ?: $city['city'] ?: $city['area'] ?: $city['region']);
		$name = str_replace(['ё'], ['е'], $name);
		[$region] = explode(' - ', $regionOverrides[$city['region']] ?? $city['region'], 2);
		$cityLocations = $locationMap[$name] ?? [];

		foreach ($cityLocations as $locationKey => $locationRow)
		{
			if (isset($known[$name]) && $known[$name] !== (string)$locationRow['CODE'])
			{
				unset($cityLocations[$locationKey]);
				continue;
			}

			$location = CSaleLocation::GetByID($locationRow['ID']);
			$locationRegion = $location['REGION_NAME'] ?? $location['CITY_NAME'];

			if (mb_stripos($locationRegion, $region) === false)
			{
				unset($cityLocations[$locationKey]);
			}
		}

		if (empty($cityLocations))
		{
			$outputMap['NOT_FOUND'][] = sprintf('[%s] %s (%s)', $city['postal_code'], $name, count($locationMap[$name] ?? []));
			continue;
		}

		if (count($cityLocations) === 1)
		{
			$locationRow = reset($cityLocations);

			$outputMap['FOUND'][$locationRow['CODE']] = [
				(int)$city['postal_code'],
				(float)$city['geo_lat'],
				(float)$city['geo_lon'],
			];
			continue;
		}

		$outputMap['FEW'][] = sprintf('[%s] %s (%s)', $city['postal_code'], $name, count($locationMap[$name] ?? []));
	}

	foreach ($outputMap as $type => $variables)
	{
		echo '<br />';
		echo $type;
		echo '<br />';
		echo sprintf('<textarea rows="10" cols="40">%s</textarea>', var_export($variables, true));
	}

	echo 'DONE';
}
catch (Main\SystemException $exception)
{
	echo $exception->getMessage();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';