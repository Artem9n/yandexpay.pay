<?php
namespace Sale\Handlers\Delivery;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Delivery;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class YandexPayHandler extends Delivery\Services\Base
{
	/**
	 * @param array $initParams
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public function __construct(array $initParams)
	{
		parent::__construct($initParams);
	}

	public static function getClassTitle() : string
	{
		return Loc::getMessage('SALE_DELIVERY_HANDLER_YANDEX_PAY_TITLE');
	}

	public static function getClassDescription() : string
	{
		return Loc::getMessage('SALE_DELIVERY_HANDLER_YANDEX_PAY_DESCRIPTION');
	}

	protected function calculateConcrete(Sale\Shipment $shipment) : Delivery\CalculationResult
	{
		return (new Delivery\CalculationResult())
			->addError(
				new Main\Error(
					Loc::getMessage('SALE_DLVR_BASE_DELIVERY_PRICE_CALC_ERROR'),
					'DELIVERY_CALCULATION'
				));
	}

	public function isCompatible(Sale\Shipment $shipment) : bool
	{
		return true;
	}
}