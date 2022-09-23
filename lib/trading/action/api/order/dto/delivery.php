<?php

namespace YandexPay\Pay\Trading\Action\Api\Order\Dto;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action;

class Delivery extends Action\Reference\Dto
{
	public function getPrice() : float
	{
		return $this->requireField('price');
	}

	public function getActualPrice() : ?float
	{
		return $this->getField('actualPrice');
	}

	public function getStatus() : string
	{
		return $this->requireField('status');
	}

	public function getCreated() : string
	{
		$date = Main\Type\DateTime::createFromPhp(
			\DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $this->requireField('created'))
		);

		$date->setDefaultTimeZone();

		return (string)$date;
	}

	public function getUpdated() : string
	{
		$date = Main\Type\DateTime::createFromPhp(
			\DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $this->requireField('updated'))
		);

		$date->setDefaultTimeZone();

		return (string)$date;
	}

	public function getData() : array
	{
		$result = [
			'PRICE' => $this->getPrice(),
		];

		$actualPrice = $this->getActualPrice();

		if ($actualPrice !== null)
		{
			$result += ['ACTUAL_PRICE' => $actualPrice];
		}

		$result += [
			'CREATED' => $this->getCreated(),
			'UPDATED' => $this->getUpdated(),
		];

		return $result;
	}
}