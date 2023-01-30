<?php

namespace YandexPay\Pay\Data\Location;

class Bounds
{
	private $cityList;
	private $distLimits;

	public function __construct (MetaData $cityList, array $distLimits = [10, 15, 20])
	{
		$this->cityList = $cityList;
		$this->distLimits = $distLimits;
	}

	public function search (
		float $swLatitude,
		float $swLongitude,
		float $neLatitude,
		float $neLongitude
	) : array
	{
		$result = $this->searchInside($swLatitude, $swLongitude, $neLatitude, $neLongitude);

		if (empty($result))
		{
			$centerLatitude = ($swLatitude + $neLatitude) / 2;
			$centerLongitude = ($swLongitude + $neLongitude) / 2;

			foreach ($this->distLimits as $limit)
			{
				$result = $this->searchUnderLimit($centerLatitude, $centerLongitude, $limit);
				if (!empty($result)){ break; }
			}
		}

		return $result;
	}

	private function searchInside(
		float $swLatitude,
		float $swLongitude,
		float $neLatitude,
		float $neLongitude
	) : array
	{
		$result = [];

		foreach ($this->cityList as $locationCode => $city) {
			if (
				$city['longitude'] >= $swLongitude && $city['longitude'] <= $neLongitude
				&& $city['latitude'] >= $swLatitude && $city['latitude'] <= $neLatitude
			) {
				$result[$locationCode] = $city;
			}
		}

		return $result;
	}

	private function searchUnderLimit(
		float $centerLatitude,
		float $centerLongitude,
		float $searchDistance
	) : array
	{
		$result = [];

		foreach ($this->cityList as $locationCode => $city) {
			$distance = $this->haversineGreatCircleDistance(
				$city['latitude'], $city['longitude'], $centerLatitude, $centerLongitude
			);

			if ($distance <= $searchDistance) {
				$result[$locationCode] = $city;
			}
		}

		return $result;
	}

	public function findClosestCity(float $pickupPointLatitude, float $pickupPointLongitude, array $cities = null): ?string
	{
		$result = null;
		$minDistance = null;

		if ($cities === null)
		{
			$cities = $this->cityList;
		}

		foreach ($cities as $locationCode => $coordinates)
		{
			$distance = $this->haversineGreatCircleDistance(
				$pickupPointLatitude,
				$pickupPointLongitude,
				$coordinates['latitude'],
				$coordinates['longitude']
			);

			if ($minDistance === null || $distance < $minDistance)
			{
				$minDistance = $distance;
				$result = $locationCode;
			}
		}

		return $result;
	}

	private function haversineGreatCircleDistance(
		float $latitudeFrom,
		float $longitudeFrom,
		float $latitudeTo,
		float $longitudeTo
	) : float {
		// convert from degrees to radians
		$latFrom = deg2rad($latitudeFrom);
		$lonFrom = deg2rad($longitudeFrom);
		$latTo = deg2rad($latitudeTo);
		$lonTo = deg2rad($longitudeTo);

		$latDelta = $latTo - $latFrom;
		$lonDelta = $lonTo - $lonFrom;

		$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
				cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
		return $angle * 6371; // km
	}
}