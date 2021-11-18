<?php

namespace YandexPay\Pay\Utils;

use Bitrix\Main;

class JsonBodyFilter implements Main\Type\IRequestFilter
{
	public function filter(array $values) : array
	{
		try
		{
			$rawInput = file_get_contents('php://input');
			$postData = Main\Web\Json::decode($rawInput);

			$result = [
				'post' => $postData,
			];
		}
		catch (\Exception $exception)
		{
			$result = [];
		}

		return $result;
	}
}