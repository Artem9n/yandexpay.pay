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

	public function getLocation($address): ?int
	{
		$result = null;

		$regions = $this->normalizedRegion($address);

		$searchMethods = [
			'Name' => [
				'fields' => [
					'locality',
					'country'
				]
			],
			'City' => [
				'fields' => [
					'locality'
				]
			],
			/*'Bounds' => [
				'fields' => [
					'ne',
					'sw'
				]
			]*/
		];

		foreach ($searchMethods as $searchMethod => $searchArgument)
		{
			$method = 'searchLocationBy' . $searchMethod;
			$payload = $this->makeSearchLocationPayload($regions, $searchArgument);

			if (empty($payload)) { continue; }

			$map = $this->{$method}($payload, $result);

			foreach ($regions as $regionIndex => $region)
			{
				if (!isset($map[$region['code']])) { continue; }

				$result = $map[$region['code']];
				array_splice($regions, $regionIndex);
				break;
			}

			if (empty($regions)) { break; }
		}

		return $result;
	}

	protected function searchLocationByBounds(array $bounds, $parentLocation = null)
	{
		/*point.coordinates.latitude >= pickupBounds.ne.latitude &&
		point.coordinates.latitude <= pickupBounds.sw.latitude &&
		point.coordinates.longitude >= pickupBounds.ne.longitude &&
		point.coordinates.longitude <= pickupBounds.sw.longitude,*/
		$result = [];

		$query = \Bitrix\Catalog\StoreTable::getList([
			'filter' => [
				[
					'LOGIC' => 'AND',
					[
						'LOGIC' => 'AND',
						['>=GPS_N' => $bounds['ne']['latitude']],
						['<=GP_N' => $bounds['sw']['latitude']]
					],
					[
						'LOGIC' => 'AND',
						['>=GPS_S' => $bounds['ne']['longitude']],
						['<=GP_S' => $bounds['sw']['longitude']]
					]
				]
			]
		]);

		//while ($)
	}

	protected function makeSearchLocationPayload($regions, $argument) : array
	{
		$fields = array_flip($argument['fields']);
		$result = [];

		foreach ($regions as $region)
		{
			if (!isset($fields[$region['code']])) { continue; }

			$result[$region['code']] = $region['name'];
		}

		return $result;
	}

	protected function normalizedRegion($address) : array
	{
		$result = [];

		$regions = array_intersect_key($address, [
			'locality' => true,
			'country' => true,
			'ne' => true,
			'sw' => true
		]);

		foreach ($regions as $code => $value)
		{
			$result[] = [
				'code' => $code,
				'name' => $value
			];
		}

		return $result;
	}

	protected function searchLocationByName(array $names, $parentLocation = null) : array
	{
		$result = [];
		$levelParents = [
			$parentLocation,
		];

		foreach (array_reverse($names, true) as $nameKey => $name)
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

	protected function searchLocationByCity(array $names, $parentLocation = null) : array
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

	protected function splitMergedName(string $name) : array
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

	protected function isMergedNameRegionPart(string $name) : bool
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