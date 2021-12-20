<?php
namespace YandexPay\Pay\Injection\Behavior;

use Bitrix\Main;
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

	abstract public function getEngineReference();

	public function getFields() : array
	{
		return [
			'POSITION' => [
				'TITLE' => self::getMessage('POSITION'),
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'VALUES' => $this->getPositionsEnum(),
			]
		];
	}

	public function getDefaults(string $siteId, array $parameters = []) : ?array
	{
		return null;
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

	public function getSelector() : string
	{
		return $this->requireValue('SELECTOR');
	}

	public function getPosition() : string
	{
		return $this->requireValue('POSITION');
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