<?php
namespace YandexPay\Pay\Injection\Behavior\Display;

use YandexPay\Pay\Utils;
use YandexPay\Pay\Reference\Concerns;

class Widget implements IDisplay
{
	use Concerns\HasMessage;

	protected $values = [];

	protected $widgetTypes = [
		'Compact',
		'BnplOffer',
		'BnplRequired',
	];

	public function getType() : string
	{
		return Registry::WIDGET;
	}

	public function getTitle() : string
	{
		return self::getMessage('TITLE');
	}

	public function setValues(array $values) : void
	{
		$this->values = $values;
	}

	public function getValues() : array
	{
		return $this->values;
	}

	public function getWidgetParameters() : array
	{
		$result = [];
		$codes = array_keys($this->getFields());

		foreach ($codes as $code)
		{
			$name = $code . '_' . mb_strtoupper($this->getType());

			if (isset($this->values[$name]))
			{
				$result[$name] = $this->values[$name];
			}
		}

		return $result;
	}

	public function getFields() : array
	{
		return [
			'TYPE' => [
				'TITLE' => self::getMessage('WIDGET_TYPE'),
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'VALUES' => $this->getWidgetTypes(),
			],
			'WIDTH_TYPE' => [
				'TITLE' => self::getMessage('WIDTH_TYPE'),
				'TYPE' => 'enumeration',
				'VALUES' => [
					[
						'ID' => static::OWN_TYPE,
						'VALUE' => self::getMessage('OWN_TYPE'),
					]
				],
				'SETTINGS' => [
					'ALLOW_NO_VALUE' => 'Y',
					'CAPTION_NO_VALUE' => self::getMessage('DEFAULT_TYPE'),
				],
			],
			'WIDTH_VALUE' => [
				'TITLE' => self::getMessage('WIDTH_VALUE'),
				'TYPE' => 'range',
				'DEPEND' => [
					'WIDTH_TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => static::OWN_TYPE,
					],
				],
				'SETTINGS' => [
					'DEFAULT_VALUE' => '360',
					'MIN' => '250',
					'MAX' => '500',
				]
			],
			'BORDER_RADIUS_TYPE' => [
				'TITLE' => self::getMessage('BORDER_RADIUS_TYPE'),
				'TYPE' => 'enumeration',
				'VALUES' => [
					[
						'ID' => static::OWN_TYPE,
						'VALUE' => self::getMessage('OWN_TYPE'),
					]
				],
				'SETTINGS' => [
					'ALLOW_NO_VALUE' => 'Y',
					'CAPTION_NO_VALUE' => self::getMessage('DEFAULT_TYPE'),
				],
			],
			'BORDER_RADIUS_VALUE' => [
				'TITLE' => self::getMessage('BORDER_RADIUS_VALUE'),
				'TYPE' => 'range',
				'DEPEND' => [
					'BORDER_RADIUS_TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => static::OWN_TYPE,
					],
				],
				'SETTINGS' => [
					'DEFAULT_VALUE' => '8',
					'MAX' => '30',
				]
			],
		];
	}

	protected function getWidgetTypes() : array
	{
		$result = [];

		foreach ($this->widgetTypes as $value)
		{
			$messageCode = mb_strtoupper($value);

			$result[] = [
				'ID' => $value,
				'VALUE' => self::getMessage('TYPE_' . $messageCode)
			];
		}

		return $result;
	}
}