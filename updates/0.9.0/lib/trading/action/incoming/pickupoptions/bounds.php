<?php
namespace YandexPay\Pay\Trading\Action\Incoming\PickupOptions;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Incoming;

class Bounds extends Incoming\Skeleton
{
	public function getNortheastLat() : float
	{
		$result = $this->requireField('ne');
		$lat = $result['latitude'];

		Assert::notNull($lat, 'northeast latitude');

		return (float)$lat;
	}

	public function getNortheastLon() : float
	{
		$result = $this->requireField('ne');
		$lon = $result['longitude'];

		Assert::notNull($lon, 'northeast longitude');

		return (float)$lon;
	}

	public function getSouthwestLat() : float
	{
		$result = $this->requireField('sw');
		$lat = $result['latitude'];

		Assert::notNull($lat, 'southwest latitude');

		return (float)$lat;
	}

	public function getSouthwestLon() : float
	{
		$result = $this->requireField('sw');
		$lon = $result['longitude'];

		Assert::notNull($lon, 'southwest longitude');

		return (float)$lon;
	}
}