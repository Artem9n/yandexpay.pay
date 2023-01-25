<?php

namespace YandexPay\Pay\Data\Location;

use YandexPay\Pay\Config;

class MetaData implements \IteratorAggregate
{
	protected $data;

	public function __construct()
	{
		$path = Config::getModulePath() . '/../resources/location/metadata.php';

		$this->data = require $path;
	}

	public function getIterator()
	{
		foreach ($this->data as $locationCode => $data)
		{
			yield $locationCode => $this->convertRaw($data);
		}
	}

	protected function convertRaw(array $raw) : array
	{
		return [
			'zip' => $raw[0],
			'latitude' => $raw[1],
			'longitude' => $raw[2],
		];
	}
}