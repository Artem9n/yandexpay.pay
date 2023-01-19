<?php

namespace YandexPay\Pay\Data\Location;

class Bounds
{
	protected $metaData;

	public function __construct(MetaData $metaData)
	{
		$this->metaData = $metaData;
	}

	public function search(array $bounds) : array
	{
		return $this->searchInside($bounds) ?: $this->searchNearest($bounds);
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

	public function searchNearest(array $bounds) : array
	{
		$result = [];
		$minDistance = null;

		foreach ($this->metaData as $locationCode => $data)
		{
			$distance = $this->distanceToBounds($data, $bounds);

			if ($minDistance === null || $distance < $minDistance)
			{
				$minDistance = $distance;
				$result = [
					$locationCode => $data,
				];
			}
		}

		return $result;
	}

	protected function distanceToBounds(array $point, array $bounds) : float
	{
		$result = null;

		foreach ($this->boundsToLines($bounds) as $line)
		{
			$distance = $this->distanceToLine(
				$point['latitude'],
				$point['longitude'],
				...$line[0],
				...$line[1]
			);

			if ($result === null || $result > $distance)
			{
				$result = $distance;
			}
		}

		return $result;
	}

	protected function boundsToLines(array $bounds) : array
	{
		$result = [];

		foreach ($bounds as $pointKey => $point)
		{
			$pointCoords = [ $point['latitude'], $point['longitude'] ];

			foreach ($bounds as $siblingKey => $sibling)
			{
				if ($pointKey !== $siblingKey) { continue; }

				$result[] = [
					$pointCoords,
					[ $point['latitude'], $sibling['longitude'] ]
				];
				$result[] = [
					$pointCoords,
					[ $sibling['latitude'], $point['longitude'] ]
				];
			}
		}

		return $result;
	}

	protected function distanceToLine($x, $y, $x1, $y1, $x2, $y2) : float
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

		$dx = $x - $xx;
		$dy = $y - $yy;

		return sqrt($dx * $dx + $dy * $dy);
	}
}