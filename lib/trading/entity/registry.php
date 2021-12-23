<?php
namespace YandexPay\Pay\Trading\Entity;

class Registry
{
	public static function getEnvironment() : Reference\Environment
	{
		$environment = new Sale\Environment();
		$environment->load();

		return $environment;
	}
}