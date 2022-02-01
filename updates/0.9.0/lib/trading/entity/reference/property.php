<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class Property
{
	protected $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * Набор свойств заказа
	 *
	 * @param int $personTypeId
	 *
	 * @return array{ID: string, VALUE: string, TYPE: string|null}[]
	 */
	public function getEnum(int $personTypeId) : array
	{
		throw new Main\NotImplementedException('getEnum is missing');
	}
}