<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery;

use Bitrix\Catalog\StoreTable;
use YandexPay\Pay;
use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

class CalculationFacade
{
	public static function mergeCalculationResult(EntityReference\Delivery\CalculationResult $result, Sale\Delivery\CalculationResult $saleResult)
	{
		$dateFrom = static::getDateFrom($saleResult);
		$dateTo = static::getDateTo($saleResult);

		$result->setDateFrom($dateFrom);
		$result->setDateTo($dateTo);
		$result->setDateIntervals(static::getDateIntervals($saleResult));
		$result->setCategory(static::getCategory($dateFrom));
		$result->setData($saleResult->getData());

		$errors = static::convertErrors($saleResult);

		if (!empty($errors))
		{
			$result->addErrors($errors);
		}

		return $result;
	}

	public static function mergeOrderData(EntityReference\Delivery\CalculationResult $result, Sale\OrderBase $order)
	{
		$price = $order->getDeliveryPrice();
		$priceRounded = static::roundPrice($price);

		$result->setPrice($priceRounded);
	}

	public static function mergeDeliveryService(EntityReference\Delivery\CalculationResult $result, Sale\Delivery\Services\Base $service)
	{
		//$stores = Sale\Delivery\ExtraServices\Manager::getStoresList($service->getId());
		//$stores = static::getStores($stores);

		$result->setDeliveryId($service->getId());
		$result->setServiceName($service->getNameWithParent());
		//$result->setStores($stores);
	}

	protected static function getStores(array $stores) : array
	{
		$result = [];

		if (empty($stores)) { return []; }

		$query = StoreTable::getList([
			'filter' => [
				'=ID' => $stores,
				'!GPS_N' => false,
				'!GPS_S' => false,
				'!ADDRESS' => false
			]
		]);

		while ($store = $query->fetch())
		{
			$result[] = $store;
		}

		return $result;
	}

	protected static function getCategory(Main\Type\DateTime $dateFrom = null) : ?string
	{
		if ($dateFrom === null) { return EntitySale\Delivery::CATEGORY_TODAY; }

		$category = EntitySale\Delivery::CATEGORY_STANDART;

		$nowDate = new Main\Type\DateTime();

		$interval = $nowDate->getDiff($dateFrom)->format('%a');

		if ($interval >= 0 && $interval <= 1)
		{
			$category = EntitySale\Delivery::CATEGORY_TODAY;
		}
		elseif ($interval >= 2 && $interval <= 7)
		{
			$category = EntitySale\Delivery::CATEGORY_EXPRESS;
		}

		return $category;
	}

	protected static function getDateFrom(Sale\Delivery\CalculationResult $saleResult)
	{
		$result = null;

		if (method_exists($saleResult, 'getPeriodFrom'))
		{
			$result = static::makePeriodDate($saleResult, $saleResult->getPeriodFrom());
		}

		if ($result === null)
		{
			$result = static::parsePeriodDescription($saleResult->getPeriodDescription());
		}

		return $result;
	}

	protected static function getDateTo(Sale\Delivery\CalculationResult $saleResult)
	{
		$result = null;

		if (method_exists($saleResult, 'getPeriodTo'))
		{
			$result = static::makePeriodDate($saleResult, $saleResult->getPeriodTo());
		}

		if ($result === null)
		{
			$result = static::parsePeriodDescription($saleResult->getPeriodDescription(), true);
		}

		return $result;
	}

	protected static function makePeriodDate(Sale\Delivery\CalculationResult $saleResult, $period)
	{
		if ((string)$period === '') { return null; }

		$period = (int)$period;

		if ($period < 0) { return null; }

		$type = $saleResult->getPeriodType();
		$interval = static::getPeriodInterval($period, $type);

		$result = new Main\Type\DateTime();
		$result->add($interval);

		return $result;
	}

	protected static function getPeriodInterval($period, $type)
	{
		$interval = 'P';

		if (
			$type === Sale\Delivery\CalculationResult::PERIOD_TYPE_HOUR
			|| $type === Sale\Delivery\CalculationResult::PERIOD_TYPE_MIN
		)
		{
			$interval .= 'T';
		}

		$interval .= (int)$period;

		if ($type === Sale\Delivery\CalculationResult::PERIOD_TYPE_MIN)
		{
			$interval .= 'M';
		}
		else
		{
			$interval .= $type;
		}

		return $interval;
	}

	protected static function parsePeriodDescription($text, $final = false)
	{
		$text = trim($text);

		if ($text === '') { return null; }

		[$from, $to] = Pay\Utils\Delivery\PeriodParser::parse($text);
		$target = $final ? $to : $from;
		$result = null;

		if ($target !== null)
		{
			$result = new Main\Type\DateTime();
			$result->add($target);
		}

		return $result;
	}

	protected static function getDateIntervals(Sale\Delivery\CalculationResult $saleResult)
	{
		$saleData = $saleResult->getData();

		if (!isset($saleData['MARKET_INTERVALS']) || !is_array($saleData['MARKET_INTERVALS'])) { return null; }

		$intervals = [];

		foreach ($saleData['MARKET_INTERVALS'] as $saleInterval)
		{
			if (!isset($saleInterval['DATE'])) { continue; }

			$date = Pay\Data\Date::sanitize($saleInterval['DATE']);
			$fromTime = isset($saleInterval['FROM_TIME']) ? Pay\Data\Time::sanitize($saleInterval['FROM_TIME']) : null;
			$toTime = isset($saleInterval['TO_TIME']) ? Pay\Data\Time::sanitize($saleInterval['TO_TIME']) : null;

			if ($date === null) { continue; }

			$interval = [
				'date' => $date,
			];

			if ($fromTime !== null && $toTime !== null)
			{
				$interval['fromTime'] = $fromTime;
				$interval['toTime'] = $toTime;
			}

			$intervals[] = $interval;
		}

		return $intervals;
	}

	protected static function convertErrors(Sale\Delivery\CalculationResult $saleResult)
	{
		$result = [];

		foreach ($saleResult->getErrors() as $error)
		{
			$result[] = new Pay\Error\Base($error->getMessage(), $error->getCode());
		}

		return $result;
	}

	protected static function roundPrice($price)
	{
		if (method_exists(Sale\PriceMaths::class, 'roundPrecision'))
		{
			$result = Sale\PriceMaths::roundPrecision($price);
		}
		else
		{
			$result = roundEx($price, 2);
		}

		return $result;
	}
}