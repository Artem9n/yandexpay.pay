<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Sale;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class Location extends EntityReference\Location
{
	use Concerns\HasMessage;

	protected $locationCache = [];
	protected $locationCacheKeys = [
		'LEFT_MARGIN' => true,
		'RIGHT_MARGIN' => true,
	];

	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function getLocation($address): int
	{
		$result = null;

		$searchMethods = [
			'Name' => true,
			'City' => true
		];

		foreach ($searchMethods as $searchMethod => $searchArgument)
		{
			$method = 'searchLocationBy' . $searchMethod;

			$map = $this->{$method}($address, $result);

			foreach ($address as $regionIndex => $region)
			{
				if (!isset($map[$regionIndex])) { continue; }

				$result = $map[$regionIndex];
			}

			$address = array_diff_key($address, $map);

			if (empty($address)) { break; }
		}

		return $result;
	}

	protected function searchLocationByName($names, $parentLocation = null) : array
	{
		$result = [];
		$levelParents = [
			$parentLocation,
		];

		foreach ($names as $nameKey => $name)
		{
			$levelMatches = [];
			$parentFilter = $this->getFewParentsLocationFilter($levelParents);

			foreach ($this->splitMergedName($name) as $namePart)
			{
				$locationId = $this->queryLocationByName($namePart, $parentFilter);

				if ($locationId === null && $this->hasNameVariableSymbols($namePart))
				{
					$locationId = $this->queryLocationByName(
						$this->makeNameVariableLike($namePart),
						$parentFilter,
						''
					);
				}

				if ($locationId === null) { continue; }

				$levelMatches[] = $locationId;
			}

			if (empty($levelMatches)) { continue; }

			$levelParents = $levelMatches;
			$result[$nameKey] = end($levelMatches);
		}

		return $result;
	}

	protected function searchLocationByCity($names, $parentLocation = null) : array
	{
		$result = [];
		$loopParent = null; // ignore previous location
		$filter = [
			'=TYPE.CODE' => 'CITY',
		];

		foreach ($names as $nameKey => $name)
		{
			$loopFilter = $filter + $this->getParentLocationFilter($loopParent);
			$locationId = $this->queryLocationByName($name, $loopFilter);

			if ($locationId === null && $this->hasNameVariableSymbols($name))
			{
				$locationId = $this->queryLocationByName(
					$this->makeNameVariableLike($name),
					$loopFilter,
					''
				);
			}

			if ($locationId === null) { continue; }

			$result[$nameKey] = $locationId;
			$loopParent = $locationId;
		}

		return $result;
	}

	protected function splitMergedName($name) : array
	{
		$glue = (string)static::getMessage('MERGED_GLUE', null, '');

		if ($glue === '') { return [ $name ]; }

		$position = mb_strpos($name, $glue);

		if ($position === false) { return [ $name ]; }

		$glueLength = mb_strlen($glue);
		$cityName =  mb_substr($name, 0, $position);
		$regionName = mb_substr($name, $position + $glueLength);

		if (!$this->isMergedNameRegionPart($regionName)) { return [ $name ]; }

		return [
			$cityName,
			$regionName
		];
	}

	protected function isMergedNameRegionPart($name) : bool
	{
		$typeName = (string)self::getMessage('MERGED_REGION');

		if ($typeName === '') { return false; }

		return mb_stripos($name, $typeName) !== false;
	}

	protected function queryLocationByName($names, array $filter = [], $compare = '=') : ?int
	{
		$result = null;

		$query = Sale\Location\LocationTable::getList(array(
			'filter' => $filter + [
					'=NAME.LANGUAGE_ID' => 'ru',
					$compare . 'NAME.NAME' => $names,
				],
			'select' => array_merge(
				[ 'ID', 'NAME' ],
				array_keys($this->locationCacheKeys)
			),
			'limit' => 1,
		));

		if ($row = $query->fetch())
		{
			$this->addLocationCache($row['ID'], $row);

			$result = (int)$row['ID'];
		}

		return $result;
	}

	protected function hasNameVariableSymbols($name) : bool
	{
		$result = false;

		foreach ($this->getVariableSymbols() as $symbol)
		{
			if (mb_stripos($name, $symbol) !== false)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	protected function makeNameVariableLike($name) : string
	{
		foreach ($this->getVariableSymbols() as $symbol)
		{
			$name = str_replace($symbol, '_', $name);
		}

		return $name;
	}

	protected function getVariableSymbols() : array
	{
		$symbols = (string)static::getMessage('VARIABLE_SYMBOLS', null, '');

		return $symbols !== '' ? explode(',', $symbols) : [];
	}

	protected function getFewParentsLocationFilter($locationIds, $context = null) : array
	{
		$filters = [];
		$count = 0;

		foreach ($locationIds as $locationId)
		{
			$filter = $this->getParentLocationFilter($locationId, $context);

			if (!empty($filter))
			{
				$filters[] = $filter;
				++$count;
			}
		}

		if ($count > 1)
		{
			$result = [
				[ 'LOGIC' => 'OR' ] + $filters
			];
		}
		else if ($count === 1)
		{
			$result = reset($filters);
		}
		else
		{
			$result = [];
		}

		return $result;
	}

	protected function getParentLocationFilter($locationId, $context = null) : array
	{
		if ($locationId === null) { return []; }

		$prefix = $context !== null ? $context . '.' : '';
		$row = $this->getLocationCache($locationId) ?: $this->fetchLocationCache($locationId);

		if ($row === null) { return []; }

		return [
			'>=' . $prefix . 'LEFT_MARGIN' => $row['LEFT_MARGIN'],
			'<=' . $prefix . 'RIGHT_MARGIN' => $row['RIGHT_MARGIN'],
		];
	}

	protected function addLocationCache($id, array $row) : void
	{
		if (isset($this->locationCache[$id])) { return; }

		$cacheValues = array_intersect_key($row, $this->locationCacheKeys);

		if (count($cacheValues) !== count($this->locationCacheKeys)) { return; }

		$this->locationCache[$id] = $cacheValues;
	}

	protected function getLocationCache($id) : ?array
	{
		return $this->locationCache[$id] ?? null;
	}

	protected function fetchLocationCache($id) : ?array
	{
		$result = null;

		$query = Sale\Location\LocationTable::getList([
			'filter' => [ '=ID' => $id ],
			'select' => array_keys($this->locationCacheKeys),
		]);

		if ($row = $query->fetch())
		{
			$this->addLocationCache($id, $row);

			$result = $row;
		}

		return $result;
	}

	public function getMeaningfulValues($locationId)
	{
		$externalData = $this->fetchLocationExternalData($locationId, [
			'ZIP' => 'ZIP',
			'ZIP_LOWER' => 'ZIP',
			'LAT' => 'LAT',
			'LATITUDE' => 'LAT',
			'LON' => 'LON',
			'LONGITUDE' => 'LON',
		]);

		return array_filter($externalData);
	}

	protected function fetchLocationExternalData($locationId, $serviceCodeMap)
	{
		$result = [];

		$query = Sale\Location\ExternalTable::getList([
			'filter' => [
				'=LOCATION.ID' => $locationId,
				'=SERVICE.CODE' => array_keys($serviceCodeMap),
			],
			'select' => [
				'XML_ID',
				'SERVICE_CODE' => 'SERVICE.CODE'
			],
		]);

		while ($row = $query->fetch())
		{
			if (!isset($serviceCodeMap[$row['SERVICE_CODE']])) { continue; }

			$dataKey = $serviceCodeMap[$row['SERVICE_CODE']];
			$xmlId = (string)$row['XML_ID'];

			if ($xmlId !== '' && !isset($result[$dataKey]))
			{
				$result[$dataKey] = $xmlId;
			}
		}

		return $result;
	}
}