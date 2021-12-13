<?php

namespace YandexPay\Pay\Trading\Entity\Reference\Delivery;

use Bitrix\Main;
use YandexPay\Pay;

class CalculationResult extends Pay\Result\Base
{
	protected $deliveryId;
	protected $deliveryType;
	protected $serviceName;
	protected $price;
	protected $dateFrom;
	protected $dateTo;
	protected $dateIntervals;
	protected $category;
	protected $outlets; /** Пункты выдачи сервиса */
	protected $stores; /** Склады Битрикс */

	public function setDeliveryId(int $deliveryId) : void
	{
		$this->deliveryId = $deliveryId;
	}

	public function getDeliveryId() : ?int
	{
		return $this->deliveryId;
	}

	/** @param string $name */
	public function setServiceName($name)
	{
		$this->serviceName = $name;
	}

	/** @return string|null */
	public function getServiceName()
	{
		return $this->serviceName;
	}

	/** @param float $price */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/** @return float|null */
	public function getPrice()
	{
		return $this->price;
	}

	public function setDateFrom(Main\Type\Date $date = null)
	{
		$this->dateFrom = $date;
	}

	public function setCategory($category) : void
	{
		$this->category = $category;
	}

	public function getCategory() : string
	{
		return $this->category;
	}

	/** @return Main\Type\Date */
	public function getDateFrom()
	{
		return $this->dateFrom ?? new Main\Type\DateTime();
	}

	public function setDateTo(Main\Type\Date $date = null)
	{
		$this->dateTo = $date;
	}

	/** @return Main\Type\Date|null */
	public function getDateTo()
	{
		return $this->dateTo;
	}

	/** @return array{date: Main\Type\Date, fromTime: string, toTime: string}[]|null */
	public function getDateIntervals()
	{
		return $this->dateIntervals;
	}

	/**  @param array{date: Main\Type\Date, fromTime: string, toTime: string}[]|null $intervals*/
	public function setDateIntervals(array $intervals = null)
	{
		$this->dateIntervals = $intervals;
	}

	/** @return string|null */
	public function getDeliveryType()
	{
		return $this->deliveryType;
	}

	public function setDeliveryType($deliveryType)
	{
		$this->deliveryType = $deliveryType;
	}

	/** @return string[]|null */
	public function getOutlets()
	{
		return $this->outlets;
	}

	/** @param string[] $outlets */
	public function setOutlets($outlets)
	{
		$this->outlets = (array)$outlets;
	}

	/** @return string[]|null */
	public function getStores()
	{
		return $this->stores;
	}

	/** @param string[] $stores */
	public function setStores($stores)
	{
		$this->stores = (array)$stores;
	}
}