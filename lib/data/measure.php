<?php

namespace YandexPay\Pay\Data;

use YandexPay\Pay\Reference\Concerns;

class Measure
{
	use Concerns\HasMessage;

	/** @var array */
	protected static $measureList;
	/** @var array */
	protected static $availableValues = [
		'0' => true,
		'10' => true,
		'11' => true,
		'12' => true,
		'20' => true,
		'21' => true,
		'22' => true,
		'30' => true,
		'31' => true,
		'32' => true,
		'40' => true,
		'41' => true,
		'42' => true,
		'50' => true,
		'51' => true,
		'70' => true,
		'71' => true,
		'72' => true,
		'73' => true,
		'80' => true,
		'81' => true,
		'82' => true,
		'83' => true,
		'NOT_MEASURE' => '255'
	];

	/**
	 * @param null|int $measure
	 *
	 * @return int
	 */
	public static function convertForService(int $measure = null) : int
	{
		$result = (int)static::$availableValues['NOT_MEASURE'];

		$measureMap = static::getMeasureMap();

		if (empty($measureMap)) { return $result; }

		$currentNameMeasure = mb_strtolower($measureMap[$measure]);

		foreach (static::$availableValues as $code => $enable)
		{
			if (!$enable) { continue; }

			$word = (string)mb_strtolower(static::getMessage('NAME_' . $code, null, ''));

			if ($word !== '' && mb_strpos($word, $currentNameMeasure) !== false)
			{
				$result = $code;
				break;
			}
		}

		return (int)$result;
	}

	protected static function getMeasureMap() : array
	{
		if (static::$measureList === null)
		{
			static::$measureList = static::loadMeasure();
		}

		return static::$measureList;
	}

	protected static function loadMeasure() : array
	{
		$result = [];

		$query = \CCatalogMeasure::getList();

		while ($measure = $query->fetch())
		{
			$result[$measure['CODE']] = $measure['MEASURE_TITLE'];
		}

		return $result;
	}
}