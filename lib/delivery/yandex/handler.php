<?php

namespace YandexPay\Pay\Delivery\Yandex;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Delivery;
use YandexPay\Pay\Reference\Concerns;

if (!Main\Loader::includeModule('sale')) { return; }

class Handler extends Sale\Delivery\Services\Base
{
	use Concerns\HasMessage;

	protected $code = 'yandex_delivery_pay';

	public static function getClassTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public static function getClassDescription() : string
	{
		return self::getMessage('DESCRIPTION');
	}

	protected function calculateConcrete(Sale\Shipment $shipment) : Delivery\CalculationResult
	{
		return (new Delivery\CalculationResult())
			->addError(
				new Main\Error(
					self::getMessage('CALC_ERROR'),
					'DELIVERY_CALCULATION'
				));
	}

	public function isCompatible(Sale\Shipment $shipment) : bool
	{
		return true;
	}

	public function getDeliveryRequestHandler() : RequestHandler
	{
		return new RequestHandler($this);
	}
}