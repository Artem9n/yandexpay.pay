<?php
namespace YandexPay\Pay\Trading\Action\Request\Address;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;

class Coordinates extends Model
{
	public function getLat()
	{
		$result = $this->getField('lat');

		Assert::notNull($result, 'lat');
		Assert::isNumber($result, 'lat');

		return $result;
	}

	public function getLon()
	{
		$result = $this->getField('lon');

		Assert::notNull($result, 'lon');
		Assert::isNumber($result, 'lon');

		return $result;
	}
}