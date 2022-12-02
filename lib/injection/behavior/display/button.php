<?php
namespace YandexPay\Pay\Injection\Behavior\Display;

use YandexPay\Pay\Utils;
use YandexPay\Pay\Reference\Concerns;

class Button implements IDisplay
{
	use Concerns\HasMessage;

	protected $values = [];

	protected $variantTypes = [
		'BLACK',
		'WHITE',
		'WHITE-OUTLINED',
	];
	protected $widthTypes = [
		'AUTO',
		'MAX',
	];

	public function getType() : string
	{
		return Registry::BUTTON;
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
			'VARIANT' => [
				'TITLE' => self::getMessage('VARIANT_TYPE'),
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'VALUES' => $this->getVariantTypes(),
			],
			'WIDTH' => [
				'TITLE' => self::getMessage('WIDTH_TYPE'),
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'VALUES' => $this->getWidthTypes(),
			],
			'WIDTH_VALUE' => [
				'TITLE' => self::getMessage('WIDTH_VALUE'),
				'TYPE' => 'range',
				'DEPEND' => [
					'WIDTH' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => static::OWN_TYPE,
					],
				],
				'SETTINGS' => [
					'DEFAULT_VALUE' => '282',
					'MIN' => '100',
					'MAX' => '600',
				]
			],
			'HEIGHT_TYPE' => [
				'TITLE' => self::getMessage('HEIGHT_TYPE'),
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
			'HEIGHT_VALUE' => [
				'TITLE' => self::getMessage('HEIGHT_VALUE'),
				'TYPE' => 'range',
				'DEPEND' => [
					'HEIGHT_TYPE' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => static::OWN_TYPE,
					],
				],
				'SETTINGS' => [
					'DEFAULT_VALUE' => '54',
					'MIN' => '40',
					'MAX' => '60',
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

	public function getVariantTypes() : array
	{
		$result = [];

		foreach ($this->variantTypes as $value)
		{
			$messageCode = str_replace('-', '_', $value);

			$result[] = [
				'ID' => $value,
				'VALUE' => self::getMessage('THEME_' . $messageCode)
			];
		}

		return $result;
	}

	public function getWidthTypes() : array
	{
		$result = [];

		foreach ($this->widthTypes as $value)
		{
			$result[] = [
				'ID' => $value,
				'VALUE' => self::getMessage('WIDTH_' . $value)
			];
		}

		$result[] = [
			'ID' => static::OWN_TYPE,
			'VALUE' => self::getMessage('OWN_TYPE')
		];

		return $result;
	}
}