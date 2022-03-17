<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupOptions\Dto;

use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Trading\Action\Reference as ActionReference;

class Coordinates extends ActionReference\Dto
{
	public function getSwLat() : float
	{
		return $this->requireField('sw.latitude');
	}

	public function getSwLon() : float
	{
		return $this->requireField('sw.longitude');
	}

	public function getNeLat() : float
	{
		return $this->requireField('ne.latitude');
	}

	public function getNeLon() : float
	{
		return $this->requireField('ne.longitude');
	}
}