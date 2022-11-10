<?php
namespace YandexPay\Pay\Injection\Behavior;

use Bitrix\Main;
use YandexPay\Pay\Ui;
use YandexPay\Pay\Utils;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Injection\Engine;
use YandexPay\Pay\Reference\Concerns;

abstract class AbstractBehavior implements BehaviorInterface
{
	use Concerns\HasMessage;

	protected $values;
	protected $insertPositions = [
		'beforebegin' => true,
		'afterbegin' => true,
		'beforeend' => true,
		'afterend' => true,
	];
	protected $variantButton = [
		'black' => true,
		'white' => true,
		'white_outlined' => true,
	];
	protected $widthButton = [
		'auto' => true,
		'max' => true,
		'custom' => true,
	];
	protected $variantCustom = 'CUSTOM';
	protected $height = '54';
	protected $borderRadius = '8';
	protected $width = '282';

	abstract public function getEngineReference();

	public function getFields() : array
	{
		return [
			'SELECTOR' => [
				'GROUP' => self::getMessage('GROUP_POSITION'),
				'TYPE' => 'string',
				'TITLE' => self::getMessage('SELECTOR'),
				'MANDATORY' => 'Y',
			],
			'POSITION' => [
				'GROUP' => self::getMessage('GROUP_POSITION'),
				'TITLE' => self::getMessage('POSITION'),
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'VALUES' => $this->getPositionsEnum(),
			],
			'USE_DIVIDER' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('GROUP_DECOR'),
				'NAME' => self::getMessage('USE_DIVIDER'),
				'SETTINGS' => [
					'DEFAULT_VALUE' => Ui\UserField\BooleanType::VALUE_FALSE,
				],
			],
			'VARIANT_BUTTON' => [
				'GROUP' => self::getMessage('GROUP_DECOR'),
				'TITLE' => self::getMessage('VARIANT_BUTTON'),
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'VALUES' => $this->getVariantButtonEnum(),
			],
			'WIDTH_BUTTON' => [
				'GROUP' => self::getMessage('GROUP_DECOR'),
				'TITLE' => self::getMessage('WIDTH_BUTTON'),
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'VALUES' => $this->getWidthButtonEnum(),
			],
			'WIDTH_VALUE' => [
				'GROUP' => self::getMessage('GROUP_DECOR'),
				'TITLE' => self::getMessage('WIDTH_VALUE'),
				'TYPE' => 'range',
				'DEPEND' => [
					'WIDTH_BUTTON' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => $this->variantCustom,
					],
				],
				'SETTINGS' => [
					'DEFAULT_VALUE' => $this->width,
					'MIN' => '100',
					'MAX' => '600',
				]
			],
			'HEIGHT_BUTTON' => [
				'GROUP' => self::getMessage('GROUP_DECOR'),
				'TITLE' => self::getMessage('HEIGHT_BUTTON'),
				'TYPE' => 'enumeration',
				'VALUES' => [
					[
						'ID' => $this->variantCustom,
						'VALUE' => self::getMessage('BUTTON_CUSTOM'),
					]
				],
				'SETTINGS' => [
					'ALLOW_NO_VALUE' => 'Y',
					'CAPTION_NO_VALUE' => self::getMessage('HEIGHT_BUTTON_DEFAULT'),
				],
			],
			'HEIGHT_VALUE' => [
				'GROUP' => self::getMessage('GROUP_DECOR'),
				'TITLE' => self::getMessage('HEIGHT_VALUE'),
				'TYPE' => 'range',
				'DEPEND' => [
					'HEIGHT_BUTTON' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => $this->variantCustom,
					],
				],
				'SETTINGS' => [
					'DEFAULT_VALUE' => $this->height,
					'MIN' => '40',
					'MAX' => '60',
				]
			],
			'BORDER_RADIUS_BUTTON' => [
				'GROUP' => self::getMessage('GROUP_DECOR'),
				'TITLE' => self::getMessage('BORDER_RADIUS_BUTTON'),
				'TYPE' => 'enumeration',
				'VALUES' => [
					[
						'ID' => $this->variantCustom,
						'VALUE' => self::getMessage('BUTTON_CUSTOM'),
					]
				],
				'SETTINGS' => [
					'ALLOW_NO_VALUE' => 'Y',
					'CAPTION_NO_VALUE' => self::getMessage('BORDER_RADIUS_BUTTON_DEFAULT'),
				],
			],
			'BORDER_RADIUS_VALUE' => [
				'GROUP' => self::getMessage('GROUP_DECOR'),
				'TITLE' => self::getMessage('BORDER_RADIUS_VALUE'),
				'TYPE' => 'range',
				'DEPEND' => [
					'BORDER_RADIUS_BUTTON' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => $this->variantCustom,
					],
				],
				'SETTINGS' => [
					'DEFAULT_VALUE' => $this->borderRadius,
					'MAX' => '30',
				]
			],
		];
	}

	public function getVariantButtonEnum() : array
	{
		$result = [];

		foreach ($this->variantButton as $code => $enable)
		{
			if (!$enable) { continue; }

			$code = mb_strtoupper($code);

			$result[] = [
				'ID' => str_replace('_', '-', $code),
				'VALUE' => self::getMessage('BUTTON_' . $code)
			];
		}

		return $result;
	}

	public function getWidthButtonEnum() : array
	{
		$result = [];

		foreach ($this->widthButton as $code => $enable)
		{
			if (!$enable) { continue; }

			$code = mb_strtoupper($code);

			$result[] = [
				'ID' => $code,
				'VALUE' => self::getMessage('BUTTON_' . $code)
			];
		}

		return $result;
	}

	protected function getPositionsEnum() : array
	{
		$result = [];

		foreach ($this->insertPositions as $code => $enable)
		{
			if (!$enable) { continue; }

			$result[] = [
				'ID' => $code,
				'VALUE' => self::getMessage('POSITION_' . mb_strtoupper($code))
			];
		}

		return $result;
	}

	public function setValues(array $values) : void
	{
		$this->values = $values;
	}

	public function getValue(string $name)
	{
		return $this->values[$name] ?? null;
	}

	public function requireValue(string $name)
	{
		$result = $this->getValue($name);

		Assert::notNull($result, sprintf('behavior[%s]', $name));

		return $result;
	}

	public function getInjectionId() : int
	{
		return (int)$this->requireValue('INJECTION_ID');
	}

	public function getSiteId() : string
	{
		return (string)$this->requireValue('SITE_ID');
	}

	public function getSelector() : string
	{
		return htmlspecialcharsback($this->requireValue('SELECTOR'));
	}

	public function getPosition() : string
	{
		return $this->requireValue('POSITION');
	}

	public function getVariant() : ?string
	{
		return $this->getValue('VARIANT_BUTTON');
	}

	public function getWidth() : ?string
	{
		return $this->getValue('WIDTH_BUTTON');
	}

	protected function isCustomWidth() : bool
	{
		$value = $this->getValue('WIDTH_BUTTON');

		return $value === 'CUSTOM';
	}

	public function getWidthValue() : ?string
	{
		$value = $this->getValue('WIDTH_VALUE');

		if ((int)$value > 0 && $this->isCustomWidth())
		{
			return $value;
		}

		return null;
	}

	protected function isCustomHeight() : bool
	{
		$value = $this->getValue('HEIGHT_BUTTON');

		return $value === 'CUSTOM';
	}

	public function getHeight() : ?string
	{
		$value = $this->getValue('HEIGHT_VALUE');

		if ((int)$value > 0 && $this->isCustomHeight())
		{
			return $value;
		}

		return null;
	}

	protected function isCustomBorderRadius() : bool
	{
		$value = $this->getValue('BORDER_RADIUS_BUTTON');

		return $value === 'CUSTOM';
	}

	public function getBorderRadius() : ?string
	{
		$value = $this->getValue('BORDER_RADIUS_VALUE');

		if ((int)$value >= 0 && $this->isCustomBorderRadius())
		{
			return $value;
		}

		return null;
	}

	public function useDivider() : bool
	{
		return (bool)$this->getValue('USE_DIVIDER');
	}

	protected function eventSettings() : array
	{
		return [];
	}

	protected function getClassEngine() : string
	{
		$engineClass = $this->getEngineReference();

		Assert::isSubclassOf($engineClass, Engine\AbstractEngine::class);

		return $engineClass;
	}

	public function install(int $injectionId) : void
	{
		/** @var Engine\AbstractEngine $classEngine */
		$classEngine = $this->getClassEngine();

		$classEngine::register([
			'module' => 'main',
			'event' => 'onEpilog',
			'arguments' => [
				$injectionId,
				$this->eventSettings(),
			],
		]);
	}

	public function uninstall(int $injectionId) : void
	{
		/** @var Engine\AbstractEngine $classEngine */
		$classEngine = $this->getClassEngine();

		try
		{
			$classEngine::unregister([
				'module' => 'main',
				'event' => 'onEpilog',
				'arguments' => [
					$injectionId,
					$this->eventSettings(),
				],
			]);
		}
		catch (Main\SystemException $exception)
		{
			// nothing
		}
	}
}