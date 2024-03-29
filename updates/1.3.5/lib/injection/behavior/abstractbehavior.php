<?php
namespace YandexPay\Pay\Injection\Behavior;

use Bitrix\Main;
use YandexPay\Pay\Ui;
use YandexPay\Pay\Utils;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Injection\Engine;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Behavior\Display;

abstract class AbstractBehavior implements BehaviorInterface
{
	use Concerns\HasMessage;

	protected $values;
	protected $display;

	protected $insertTypes = [
		'beforebegin',
		'afterbegin',
		'beforeend',
		'afterend',
	];

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
			'DISPLAY' => [
				'TITLE' => self::getMessage('DISPLAY'),
				'GROUP' => self::getMessage('GROUP_DECOR'),
				'TYPE' => 'enumeration',
				'VALUES' => $this->getDisplayList(),
				'SETTINGS' => [
					'DEFAULT_VALUE' => Display\Registry::BUTTON,
				],
			],
		] + $this->getDisplayFields();
	}

	protected function getDisplayFields() : array
	{
		$result = [];

		foreach (Display\Registry::getTypes() as $type)
		{
			$display = Display\Registry::create($type);
			$prefixName = mb_strtoupper($type);

			foreach ($display->getFields() as $name => $field)
			{
				$fieldName = sprintf('%s_%s', $name, $prefixName);

				$depend = [
					'DISPLAY' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => [ $type ],
					],
				];

				$field['GROUP'] = self::getMessage('GROUP_DECOR');

				if (isset($field['DEPEND']))
				{
					$dependField = [];

					foreach ($field['DEPEND'] as $code => $dependVal)
					{
						$dependName = sprintf('%s_%s', $code, $prefixName);
						$dependField[$dependName] = $dependVal;
					}

					$field['DEPEND'] = $dependField + $depend;
				}
				else
				{
					$field['DEPEND'] = $depend;
				}

				$result[$fieldName] = $field;
			}
		}

		return $result;
	}

	protected function getDisplayList() : array
	{
		$result = [];

		foreach (Display\Registry::getTypes() as $type)
		{
			$display = Display\Registry::create($type);
			$result[] = [
				'ID' => $type,
				'VALUE' => $display->getTitle(),
			];
		}

		return $result;
	}

	protected function getPositionsEnum() : array
	{
		$result = [];

		foreach ($this->insertTypes as $value)
		{
			$result[] = [
				'ID' => $value,
				'VALUE' => self::getMessage('POSITION_' . mb_strtoupper($value))
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

	public function useDivider() : bool
	{
		return (bool)$this->getValue('USE_DIVIDER');
	}

	public function getDisplay() : Display\IDisplay
	{
		if ($this->display === null)
		{
			$this->display = $this->createDisplay();
		}

		return $this->display;
	}

	protected function createDisplay() : Display\IDisplay
	{
		$type = $this->getValue('DISPLAY') ?? Display\Registry::BUTTON;

		$display = Display\Registry::create($type);
		$display->setValues($this->values);

		return $display;
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