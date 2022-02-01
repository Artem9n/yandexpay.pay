<?php

namespace YandexPay\Pay\Utils;

use Bitrix\Main;
use YandexPay\Pay\Gateway\Base;

class Secure3dBodyFilter implements Main\Type\IRequestFilter
{
	public function filter(array $values) : array
	{
		$result = $values;
		/*$secure = $values['get']['secure3ds'] ?? $values['post']['secure3ds'];

		if (isset($secure))
		{
			$result['post'] += Base::parseParams($secure);
		}*/

		return $result;
	}
}