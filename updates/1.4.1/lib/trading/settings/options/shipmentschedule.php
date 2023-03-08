<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Settings;
use YandexPay\Pay\Trading\Entity;

class ShipmentSchedule extends Settings\Reference\Fieldset
{
	use Concerns\HasMessage;

	public function getTimeZoneOffset() : int
	{
		$date = new Main\Type\DateTime();

		return (int)$date->format('Z') / 60;
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getSchedule() : ScheduleOptions
	{
		return $this->getFieldsetCollection('SCHEDULE');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getHoliday() : HolidayOption
	{
		return $this->getFieldset('HOLIDAY');
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return
			$this->getSelfFields($environment, $siteId)
			+ $this->getHolidayFields($environment, $siteId);
	}

	protected function getSelfFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'SCHEDULE' => $this->getSchedule()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'NAME' => self::getMessage('SCHEDULE'),
				'GROUP' => self::getMessage('SCHEDULE_GROUP'),
				'HELP' => self::getMessage('SCHEDULE_HELP'),
			],
		];
	}

	protected function getHolidayFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return $this->getHoliday()->getHolidayFields($environment, $siteId);
	}

	protected function getFieldsetCollectionMap() : array
	{
		return [
			'SCHEDULE' => ScheduleOptions::class,
		];
	}

	protected function getFieldsetMap() : array
	{
		return [
			'HOLIDAY' => HolidayOption::class,
		];
	}
}