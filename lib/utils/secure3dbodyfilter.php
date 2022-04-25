<?php

namespace YandexPay\Pay\Utils;

use Bitrix\Main;

class Secure3dBodyFilter implements Main\Type\IRequestFilter
{
	public function filter(array $values) : array
	{
		$result = $values;

		return $result;
	}
}