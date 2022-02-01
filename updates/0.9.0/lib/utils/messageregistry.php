<?php

namespace YandexPay\Pay\Utils;

use Bitrix\Main;

class MessageRegistry
{
	private static $moduleInstance;

	private $classFinder;
	private $prefix;
	private $prefixes = [];
	private $included = [];

	public static function getModuleInstance(): MessageRegistry
	{
		if (static::$moduleInstance === null)
		{
			static::$moduleInstance = new static(
				ClassFinder::forModule()
			);
		}

		return static::$moduleInstance;
	}

	public function __construct(ClassFinder $classFinder, $prefix = '')
	{
		$this->classFinder = $classFinder;
		$this->prefix = $prefix;
	}

	public function load($className): void
	{
		if (isset($this->included[$className])) { return; }

		$path = $this->classFinder->getPath($className);

		Main\Localization\Loc::loadMessages($path);
		$this->included[$className] = true;
	}

	public function getPrefix($className) : string
	{
		if (!isset($this->prefixes[$className]))
		{
			$this->prefixes[$className] = $this->makePrefix($className);
		}

		return $this->prefixes[$className];
	}
	
	private function makePrefix($className): string
	{
		$relativeName = $this->classFinder->getRelativeName($className);
		$prefix = str_replace('\\', '_', $relativeName);
		$prefix = preg_replace('/([A-Z]+)/', '_$1', $prefix);
		$prefix = preg_replace('/__+/', '_', $prefix);
		$prefix = ltrim($prefix, '_');
		$prefix = mb_strtoupper($prefix);

		return $this->prefix . $prefix;
	}
}