<?php

namespace YandexPay\Pay\GateWay;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Application;
use Bitrix\Sale\Internals\PaySystemActionTable;
use YandexPay\Pay\Config;

class Manager
{
	protected static $handlerMode;
	protected static $handlerDescription;

	public static function getHandlerModeList(): array
	{
		$result = [];
		$sort = [];

		$classListGateWay = static::getClassList();

		if (empty($classListGateWay)) { return $result; }

		foreach ($classListGateWay as $classGateWay)
		{
			/** @var \YandexPay\Pay\GateWay\Base $gateWay */
			$gateWay = new $classGateWay();

			$gateWayId = $gateWay->getId();
			$gateWayName = $gateWay->getName();

			$sort[$gateWayId] = $gateWay->getSort();
			$result[$gateWayId] = $gateWayName;
		}

		uksort($result, static function($a, $b) use ($sort){
			return $sort[$a] > $sort[$b];
		});

		return $result;
	}

	protected static function getClassList(): array
	{
		$baseClassName = Base::class;

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

		$request = Main\Context::getCurrent()->getRequest();
		$handlerMode = $request->get('PS_MODE');
		$systemId = $request->get('ID');

		if (isset($handlerModeList[$handlerMode]))
		{
			$type = $handlerMode;
		}
		else
		{
			$type = static::getHandlerMode($systemId);
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

	protected static function getHandlerMode($systemId): string
	{
		if (static::$handlerMode === null)
		{
			static::$handlerMode = static::loadHandlerMode($systemId);
		}

		return static::$handlerMode;
	}

	public static function getModeDescription()
	{
		return static::$handlerDescription;
	}

	protected static function loadHandlerMode($systemId): string
	{
		$result = '';
		$systemId = $systemId ?? 'yandexpay';

		$query = PaySystemActionTable::getList([
			'filter' => [
				[
					'LOGIC' => 'OR',
					['=ID' => $systemId],
					['=ACTION_FILE' => $systemId]
				]
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


	public static function getProvider(string $type, Sale\Payment $payment = null, Main\Request $request = null): Base
	{
		$className = '\\' . __NAMESPACE__ . '\\Payment\\' . ucfirst($type);

		if (!class_exists($className))
		{
			throw new \Bitrix\Main\ObjectNotFoundException('gateway not found');
		}

		return new $className($payment, $request);
	}
}