<?php

namespace Yandexpay\Pay\GateWay;

use Bitrix\Main\Application;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Yandexpay\Pay\Config;

class Manager
{
	protected static $handlerMode;
	protected static $handlerDescription;

	public static function getHandlerModeList(): array
	{
		$result = [];

		$classListGateWay = static::getClassList();

		if (empty($classListGateWay)) { return $result; }

		foreach ($classListGateWay as $classGateWay)
		{
			/** @var \Yandexpay\Pay\GateWay\Base $gateWay */
			$gateWay = new $classGateWay();

			$gateWayId = $gateWay->getId();
			$gateWayName = $gateWay->getName();

			$result[$gateWayId] = $gateWayName;
		}

		return $result;
	}

	protected static function getClassList(): array
	{
		$baseClassName = Base::getClassName();

		$baseDir = Config::getModulePath();
		$baseNamespace = Config::getNamespace();
		$directory = new \RecursiveDirectoryIterator($baseDir);
		$iterator = new \RecursiveIteratorIterator($directory);
		$result = [];

		/** @var \DirectoryIterator $entry */
		foreach ($iterator as $entry)
		{
			if (
				$entry->isFile()
				&& $entry->getExtension() === 'php'
			)
			{
				$relativePath = str_replace($baseDir, '', $entry->getPath());
				$className = $baseNamespace . str_replace('/', '\\', $relativePath) . '\\' . $entry->getBasename('.php');

				if (
					!empty($relativePath)
					&& class_exists($className)
					&& is_subclass_of($className, $baseClassName)
				)
				{
					$result[] = $className;
				}
			}
		}

		return $result;
	}

	public static function getParams(): array
	{
		$handlerModeList = static::getHandlerModeList();

		if (empty($handlerModeList)) { return []; }

		$type = '';

		$application = Application::getInstance();

		if ($application !== null)
		{
			$request = $application->getContext()->getRequest();
			$requestMode = $request->get('PS_MODE');

			if ($requestMode !== null && isset($handlerModeList[$requestMode]))
			{
				$type = $requestMode;
			}
			else
			{
				$type = static::getHandlerMode();
			}
		}

		if ($type === '')
		{
			reset($handlerModeList);

			$type = key($handlerModeList);
		}

		$gateWay = static::getProvider($type);

		static::$handlerDescription = $gateWay->getDescription();

		return $gateWay->getParams();
	}

	protected static function getHandlerMode(): string
	{
		if (static::$handlerMode === null)
		{
			static::$handlerMode = static::loadHandlerMode();
		}

		return static::$handlerMode;
	}

	public static function getModeDescription()
	{
		return static::$handlerDescription;
	}

	protected static function loadHandlerMode(): string
	{
		$result = '';

		$query = PaySystemActionTable::getList([
			'filter' => [
				'=ACTION_FILE' => 'yandexpay'
			],
			'select' => ['ID', 'PS_MODE'],
			'limit' => 1
		]);

		if ($paySystem = $query->fetch())
		{
			$result = $paySystem['PS_MODE'];
		}

		return $result;
	}


	public static function getProvider(string $type): Base
	{
		$className = '\\' . __NAMESPACE__ . '\\Payment\\' . ucfirst($type);

		if (!class_exists($className))
		{
			throw new \Bitrix\Main\ObjectNotFoundException('gateway not found');
		}

		return new $className();
	}
}