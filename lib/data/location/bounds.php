<?php

namespace YandexPay\Pay\Data\Location;

class Bounds
{
	protected $bounds;
	protected $metaData;
	protected $distLimits = [
		10, 15, 20, //km
	];

	public function __construct(MetaData $metaData)
	{
		$this->metaData = $metaData;
	}

	public function search(array $bounds) : array
	{
		$result = $this->searchInside($bounds);

		if (empty($result))
		{
			$this->boundsNormalize($bounds);
			$boundsDistance = $this->boundsDistance();

			foreach ($this->distLimits as $limit)
			{
				$result = $this->searchUnderLimit(min($limit, $boundsDistance));
				if (!empty($result)) { break; }
			}
		}

		return $result;
	}

	public function searchInside(array $bounds) : array
	{
		$result = [];

		foreach ($this->metaData as $locationCode => $data)
		{
			if (
				$data['longitude'] >= $bounds['sw']['longitude'] && $data['longitude'] <= $bounds['ne']['longitude']
				&& $data['latitude'] >= $bounds['sw']['latitude'] && $data['latitude'] <= $bounds['ne']['latitude']
			)
			{
				$result[$locationCode] = $data;
			}
		}

		return $result;
	}

	public function searchUnderLimit(float $limit) : array
	{
		$result = [];

		foreach ($this->metaData as $locationCode => $data)
		{
			$distance = $this->distanceToBounds($data);

			if ($distance <= $limit)
			{
				$result[$locationCode] = $data;
			}
		}

		return $result;
	}

	protected function distanceToBounds(array $point) : float
	{
		return $this->distanceToLine(
			$point['latitude'],
			$point['longitude'],
			...$this->bounds[0],
			...$this->bounds[1]
		);
	}

	protected function boundsDistance() : float
	{
		return $this->distanceBetweenPoints(...$this->bounds[0], ...$this->bounds[1]);
	}

	protected function boundsNormalize(array $bounds) : void
	{
		foreach ($bounds as $point)
		{
			$this->bounds[] = [ $point['latitude'], $point['longitude'] ];
		}
	}

	protected function distanceToLine(
		float $x,
		float $y,
		float $x1,
		float $y1,
		float $x2,
		float $y2
	) : float
	{
		$A = $x - $x1;
		$B = $y - $y1;
		$C = $x2 - $x1;
		$D = $y2 - $y1;

		$dot = $A * $C + $B * $D;
		$len_sq = $C * $C + $D * $D;
		$param = -1;

		if ((float)$len_sq !== 0.0)
		{
			$param = $dot / $len_sq;
		}

		if ($param < 0)
		{
			$xx = $x1;
			$yy = $y1;
		}
		else if ($param > 1)
		{
			$xx = $x2;
			$yy = $y2;
		}
		else
		{
			$xx = $x1 + $param * $C;
			$yy = $y1 + $param * $D;
		}

		return $this->distanceBetweenPoints($x, $y, $xx, $yy);
	}

	public function distanceBetweenPoints(
		float $lat1,
		float $lon1,
		float $lat2,
		float $lon2
	) : float
	{
		$R = 6371; // Radius of the earth in km
		$dLat = deg2rad($lat2-$lat1);  // deg2rad below
		$dLon = deg2rad($lon2-$lon1);
		$a =
			sin($dLat/2) * sin($dLat/2) +
			cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
			sin($dLon/2) * sin($dLon/2)
		;
		$c = 2 * atan2(sqrt($a), sqrt(1-$a));

		return $R * $c; // Distance in km
	}
}