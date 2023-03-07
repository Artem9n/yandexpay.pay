<?php

namespace YandexPay\Pay\Trading\Settings\Options\Courier;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings;
use YandexPay\Pay\Utils;

class Options extends Settings\Reference\Fieldset
{
	use Concerns\HasMessage;

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getTimeInterval() : Settings\Options\IntervalOption
	{
		return $this->getFieldset('TIME_INTERVAL');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getTimeIntervals() : Settings\Options\IntervalOptions
	{
		return $this->getFieldsetCollection('TIME_INTERVAL_VALUES');
	}

	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getDateInterval() : DateInterval
	{
		return $this->getFieldset('DATE_INTERVAL');
	}

	public function getTypeSchedule() : string
	{
		return $this->requireValue('TYPE_SCHEDULE');
	}

	public function getTypeTimeInterval() : ?string
	{
		return $this->getValue('TYPE_TIME_INTERVALS') ?: null;
	}

	public function getDuration() : string
	{
		return $this->requireValue('DURATION_GRID');
	}

	public function getStartTime() : string
	{
		return $this->requireValue('START_TIME_GRID');
	}

	public function getEndTime() : string
	{
		return $this->requireValue('END_TIME_GRID');
	}

	public function getStepTime() : string
	{
		return $this->requireValue('STEP_TIME_GRID');
	}

	protected function getRequiredGrid() : array
	{
		return [
			'DURATION_GRID', 'START_TIME_GRID', 'END_TIME_GRID', 'STEP_TIME_GRID'
		];
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'TYPE_SCHEDULE' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('TYPE_SCHEDULE'),
				'HELP' => self::getMessage('TYPE_SCHEDULE_HELP'),
				'VALUES' => [
					[
						'ID' => 'PLAIN',
						'VALUE' => self::getMessage('PLAIN'),
					],
					[
						'ID' => 'FLEXIBLE',
						'VALUE' => self::getMessage('FLEXIBLE'),
					],
				],
			],
			'DATE_INTERVAL' => $this->getDateInterval()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'NAME' => self::getMessage('DATE_INTERVAL'),
				'HELP' => self::getMessage('DATE_INTERVAL_HELP'),
			],
			'TIME_INTERVAL' => $this->getTimeInterval()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'NAME' => self::getMessage('TIME_INTERVAL'),
				'HELP' => self::getMessage('TIME_INTERVAL_HELP'),
				'DEPEND' => [
					'TYPE_SCHEDULE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'PLAIN',
					],
				]
			],
			'TYPE_TIME_INTERVALS' => [
				'TYPE' => 'enumeration',
				'NAME' => self::getMessage('TYPE_TIME_INTERVALS'),
				'HELP' => self::getMessage('TYPE_TIME_INTERVALS_HELP'),
				'VALUES' => [
					[
						'ID' => 'GRID',
						'VALUE' => self::getMessage('GRID'),
					],
					[
						'ID' => 'VALUES',
						'VALUE' => self::getMessage('VALUES'),
					],
				],
				'DEPEND' => [
					'TYPE_SCHEDULE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'FLEXIBLE',
					],
				],
				'SETTINGS' => [
					'CAPTION_NO_VALUE' => self::getMessage('NO_VALUE'),
				],
			],
			'DURATION_GRID' => [
				'TYPE' => 'time',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('DURATION_GRID'),
				'DEPEND' => [
					'TYPE_SCHEDULE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'FLEXIBLE',
					],
					'TYPE_TIME_INTERVALS' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'GRID',
					],
				],
			],
			'START_TIME_GRID' => [
				'TYPE' => 'time',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('START_TIME_GRID'),
				'DEPEND' => [
					'TYPE_SCHEDULE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'FLEXIBLE',
					],
					'TYPE_TIME_INTERVALS' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'GRID',
					],
				],
			],
			'END_TIME_GRID' => [
				'TYPE' => 'time',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('END_TIME_GRID'),
				'DEPEND' => [
					'TYPE_SCHEDULE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'FLEXIBLE',
					],
					'TYPE_TIME_INTERVALS' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'GRID',
					],
				],
			],
			'STEP_TIME_GRID' => [
				'TYPE' => 'time',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('STEP_TIME_GRID'),
				'DEPEND' => [
					'TYPE_SCHEDULE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'FLEXIBLE',
					],
					'TYPE_TIME_INTERVALS' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'GRID',
					],
				],
			],
			'TIME_INTERVAL_VALUES' => $this->getTimeIntervals()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('TIME_INTERVAL_VALUES'),
				'HELP' => self::getMessage('TIME_INTERVAL_VALUES_HELP'),
				'DEPEND' => [
					'TYPE_SCHEDULE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'FLEXIBLE',
					],
					'TYPE_TIME_INTERVALS' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => 'VALUES',
					],
				],
			],
		];
	}

	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return parent::getFieldDescription($environment, $siteId) + [
			'SETTINGS' => [
				'SUMMARY' => '#TYPE_SCHEDULE#, #TYPE_TIME_INTERVALS#',
				'LAYOUT' => 'summary',
				'MODAL_WIDTH' => 650,
				'MODAL_HEIGHT' => 400,
			],
		];
	}

	protected function getFieldsetMap() : array
	{
		return [
			'TIME_INTERVAL' => Settings\Options\IntervalOption::class,
			'DATE_INTERVAL' => DateInterval::class,
		];
	}

	protected function getFieldsetCollectionMap() : array
	{
		return [
			'TIME_INTERVAL_VALUES' => Settings\Options\IntervalOptions::class
		];
	}

	public function validate() : Main\Result
	{
		$result = new Main\Entity\Result();
		$typeSchedule = $this->getTypeSchedule();
		$typeTimeInterval = $this->getTypeTimeInterval();

		if ($typeSchedule === 'FLEXIBLE')
		{
			if ($typeTimeInterval === 'GRID')
			{
				foreach ($this->getRequiredGrid() as $code)
				{
					if (!empty($this->getValue($code))) { continue; }
					$result->addError(new Main\Error(static::getMessage(sprintf('FIELD_%s_REQUIRED', $code))));
				}
			}
			else if ($typeTimeInterval === 'VALUES' && empty($this->getTimeIntervals()->getValues()))
			{
				$result->addError(new Main\Error(static::getMessage('FIELD_TIME_INTERVALS_REQUIRED')));
			}
		}

		return $result;
	}
}