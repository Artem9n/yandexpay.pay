<?php

namespace YandexPay\Pay\Data;

class Quantity
{
	public static function round($value) : float
	{
		return round($value, 2);
	}
}