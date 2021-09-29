<?php

namespace YandexPay\Pay\Utils;

class Morph
{
	public static function decline($number, array $titles) : string
	{
		$cases = [2, 0, 1, 1, 1, 2];

		return (string)$titles[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
	}
}