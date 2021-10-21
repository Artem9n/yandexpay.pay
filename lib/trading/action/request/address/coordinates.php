<?php
namespace YandexPay\Pay\Trading\Action\Request\Address;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

class Coordinates extends Model
{
	public function getLat()
	{
		$result = $this->getField('latitude');

		Assert::notNull($result, 'latitude');
		Assert::isNumber($result, 'latitude');

		return $result;
	}

	public function getLon()
	{
		$result = $this->getField('longitude');

		Assert::notNull($result, 'longitude');
		Assert::isNumber($result, 'longitude');

		return $result;
	}
}