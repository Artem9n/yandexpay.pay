<?php

namespace YandexPay\Pay\Data;

class Price
{
	public static function round($value) : float
	{
		return round($value, 2);
	}
}