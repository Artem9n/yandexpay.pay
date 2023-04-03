<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Sdek;

use Bitrix\Sale;

/** @property Sale\Delivery\Services\AutomaticProfile $service */
class Postamat extends Pickup
{
	protected $code = 'POSTAMAT';
	protected $codeService = 'sdek:postamat';
	protected $tariff = 'postamat';
}