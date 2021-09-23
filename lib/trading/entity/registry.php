<?php
namespace YandexPay\Pay\Trading\Entity;

class Registry
{
	public static function getEnvironment() : Reference\Environment
	{
		return new Sale\Environment();
	}
}