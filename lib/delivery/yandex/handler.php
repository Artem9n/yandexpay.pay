<?php

namespace YandexPay\Pay\Delivery\Yandex;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Delivery;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading\Entity\Sale\Platform as TradingPlatform;

if (!Main\Loader::includeModule('sale')) { return; }

class Handler extends Sale\Delivery\Services\Base
{
	use Concerns\HasMessage;

	public const CODE = 'yandex_delivery_pay';

	protected $code = self::CODE;

	public static function getClassTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public static function getClassDescription() : string
	{
		return self::getMessage('DESCRIPTION');
	}

	public function prepareFieldsForSaving(array $fields) : array
	{
		$fields['CODE'] = self::CODE;

		return parent::prepareFieldsForSaving($fields);
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
		$order = $shipment->getOrder();

		if ($order === null) { return false; }

		$tradingPlatform = $this->tradingPlatformOrder($order->getTradeBindingCollection());
		$platformId = $this->getPlatformId();

		if ($platformId === null) { return false; }

		return in_array($platformId, $tradingPlatform, true);
	}

	protected function getPlatformId() : ?int
	{
		$platform = Sale\TradingPlatformTable::getList([
			'filter' => [
				'=CODE' => TradingPlatform::TRADING_PLATFORM_CODE
			],
			'limit' => 1,
		])->fetch();

		return $platform['ID'] ?? null;
	}

	protected function tradingPlatformOrder(Sale\TradeBindingCollection $collection) : array
	{
		$result = [];

		/** @var Sale\TradeBindingEntity $trading */
		foreach ($collection as $trading)
		{
			$platformId = (int)$trading->getField('TRADING_PLATFORM_ID');

			if ($platformId <= 0) { continue; }

			$result[] = $platformId;
		}

		return $result;
	}

	public function getDeliveryRequestHandler() : RequestHandler
	{
		return new RequestHandler($this);
	}

	public static function onAfterAdd($serviceId, array $fields = array()) : void
	{
		static::installRestriction($serviceId);
		ShipmentRequestMarker::install();
	}

	public static function onAfterDelete($serviceId) : void
	{
		try
		{
			Sale\Delivery\Services\Manager::getObjectByCode(self::CODE);
		}
		catch (Main\SystemException $exception)
		{
			ShipmentRequestMarker::uninstall();
		}
	}

	protected static function installRestriction($serviceId) : void
	{
		$result = Sale\Internals\ServiceRestrictionTable::add([
			'SERVICE_ID' => $serviceId,
			'SERVICE_TYPE' => 0,
			'CLASS_NAME' => '\\' . Trading\UseCase\Restrictions\ByPlatform\Delivery::class,
			'PARAMS' => [
				'VIEW' => 'YANDEX_CHECKOUT',
			],
		]);

		Exceptions\Facade::handleResult($result);
	}
}