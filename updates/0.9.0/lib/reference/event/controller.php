<?php

namespace YandexPay\Pay\Reference\Event;

use Bitrix\Main;
use YandexPay\Pay\Config;

class Controller
{
	/**
	 * Добавляем обработчик
	 *
	 * @param string $className
	 * @param array $handlerParams
	 *
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 * */
	public static function register($className, $handlerParams): void
	{
		$handlerDescription = static::getHandlerDescription($className, $handlerParams);

		static::saveHandler($handlerDescription);
	}

	/**
	 * Удаляем обработчик
	 *
	 * @param string $className
	 * @param array $handlerParams
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public static function unregister($className, $handlerParams): void
	{
		$handlerDescription = static::getHandlerDescription($className, $handlerParams);
		$registeredList = static::getRegisteredHandlers($className);
		$handlerKey = static::getHandlerKey($handlerDescription, true);

		if (isset($registeredList[$handlerKey]))
		{
			static::deleteHandler($registeredList[$handlerKey]);
		}
	}

	/**
	 * Обновляет привязки регулярных обработчиков событий
	 *
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 * */
	public static function updateRegular(): void
	{
		$baseClassName = Regular::getClassName();

		$classList = static::getClassList($baseClassName);
		$handlerList = static::getClassHandlers($classList);
		$registeredList = static::getRegisteredHandlers($baseClassName);

		static::saveHandlers($handlerList);
		static::deleteHandlers($handlerList, $registeredList);
	}

	/**
	 * Удаляем все события
	 *
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public static function deleteAll(): void
	{
		$namespace = Config::getNamespace();
		$registeredList = static::getRegisteredHandlers($namespace, true);

		static::deleteHandlers([], $registeredList);
	}

	/**
	 * Обходит список классов и готовит массив для записи
	 *
	 * @param $classList array список классов
	 *
	 * @return array список обработчиков для регистрации
	 * @throws Main\NotImplementedException
	 * @throws Main\ArgumentException
	 */
	protected static function getClassHandlers($classList): array
	{
		$result = [];

		/** @var Regular $className */
		foreach ($classList as $className)
		{
			$normalizedClassName = $className::getClassName();
			$handlers = $className::getHandlers();

			foreach ($handlers as $handler)
			{
				$handlerDescription = static::getHandlerDescription(
					$normalizedClassName,
					$handler
				);
				$handlerKey = static::getHandlerKey($handlerDescription, true);

				$result[$handlerKey] = $handlerDescription;
			}
		}

		return $result;
	}

	/**
	 * Возвращает список всех подклассов неймспейса Event,
	 * используется для регистрации всех обработчиков в updateRegular().
	 *
	 * @param string название класса, наследники которого надо вернуть
	 *
	 * @return array список имён классов для обхода
	 * */
	protected static function getClassList($baseClassName): array
	{
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
				$tableClassName = $className . 'Table';

				if (
					!empty($relativePath)
					&& !class_exists($tableClassName)
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

	/**
	 * Возвращает описание обработчика для регистрации, проверяет существование метода
	 *
	 * @param $className string
	 * @param $handlerParams array|null параметры обработчика
	 *
	 * @return array
	 * @throws Main\NotImplementedException
	 * @throws Main\ArgumentException
	 */
	protected static function getHandlerDescription($className, $handlerParams): array
	{
		if (empty($handlerParams['module']) || empty($handlerParams['event']))
		{
			throw new Main\ArgumentException(
				'Require module and event param in ' . $className
			);
		}

		$method = $handlerParams['method'] ?? $handlerParams['event'];

		if (!method_exists($className, $method))
		{
			throw new Main\NotImplementedException(
				'Method ' . $method
				. ' not defined in ' . $className
				. ' and cannot be registered as event handler'
			);
		}

		return array(
			'module'    => $handlerParams['module'],
			'event'     => $handlerParams['event'],
			'class'     => $className,
			'method'    => $method,
			'sort'      => isset($handlerParams['sort']) ? (int)$handlerParams['sort'] : 100,
			'arguments' => $handlerParams['arguments'] ?? ''
		);
	}

	/**
	 * Получаем список ранее зарегистрированных обработчиков
	 *
	 * @param $baseClassName string название класса, наследников которого необходимо получить
	 * @param $isBaseNamespace bool первый аргумент не является классом
	 *
	 * @return array список зарегистрированных обработчиков
	 * @throws Main\Db\SqlQueryException
	 * */
	protected static function getRegisteredHandlers($baseClassName, $isBaseNamespace = false): array
	{
		$registeredList = array();
		$namespaceLower = str_replace('\\', '\\\\', mb_strtolower(Config::getNamespace()));
		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$query = $connection->query(
			'SELECT * FROM b_module_to_module WHERE TO_CLASS like "' . $sqlHelper->forSql($namespaceLower) . '%"'
		);

		while ($handlerRow = $query->fetch())
		{
			$handlerClassName = $handlerRow['TO_CLASS'];

			if (
				$isBaseNamespace
				|| $handlerClassName === $baseClassName
				|| !class_exists($handlerClassName)
				|| is_subclass_of($handlerClassName, $baseClassName)
			)
			{
				$handlerKey = static::getHandlerKey($handlerRow);
				$registeredList[$handlerKey] = $handlerRow;
			}
		}

		return $registeredList;
	}

	/**
	 * Получаем зарегистрированный обработчик
	 *
	 * @param $handlerDescription array
	 *
	 * @return array|null обработчик обработчик
	 * @throws Main\Db\SqlQueryException
	 * */
	public static function getRegisteredHandler($handlerDescription): ?array
	{
		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$query = $connection->query(
			'SELECT * FROM b_module_to_module'
			. ' WHERE '
				. ' FROM_MODULE_ID = "' . $sqlHelper->forSql($handlerDescription['module']) . '"'
				. ' MESSAGE_ID = "' . $sqlHelper->forSql($handlerDescription['event']) . '"'
				. ' TO_CLASS = "' . $sqlHelper->forSql($handlerDescription['class']) . '"'
				. ' TO_METHOD = "' . $sqlHelper->forSql($handlerDescription['method']) . '"'
		);

		return $query->fetch() ?: null;
	}

	/**
	 * Ключ массива для обработчика события
	 *
	 * @param $handlerData array
	 * @param $byDescription boolean генерировать ключ по описанию или зарегистрированнуму обработчику
	 *
	 * @return string
	 */
	protected static function getHandlerKey($handlerData, $byDescription = false): string
	{
		$signKeys = array(
			'module'    => 'FROM_MODULE_ID',
			'event'     => 'MESSAGE_ID',
			'class'     => 'TO_CLASS',
			'method'    => 'TO_METHOD',
			'arguments' => 'TO_METHOD_ARG'
		);
		$values = array();

		foreach ($signKeys as $descriptionKey => $rowKey)
		{
			$key = $byDescription ? $descriptionKey : $rowKey;
			$values[] = is_array($handlerData[$key]) && !empty($handlerData[$key]) ? serialize($handlerData[$key]) : $handlerData[$key];
		}

		return mb_strtolower(implode('|', $values));
	}

	/**
	 * Регистрирует все обработчики в базе данных
	 *
	 * @param $handlerList array список обработчиков для регистрации
	 */
	protected static function saveHandlers($handlerList): void
	{
		foreach ($handlerList as $handlerKey => $handlerDescription)
		{
			static::saveHandler($handlerDescription);
		}
	}

	/**
	 * Регистрируем обработчик в базе данных
	 *
	 * @param $handlerDescription array обработчик
	 * */
	protected static function saveHandler($handlerDescription): void
	{
		$eventManager = Main\EventManager::getInstance();

		$eventManager->registerEventHandler(
			$handlerDescription['module'],
			$handlerDescription['event'],
			Config::getModuleName(),
			$handlerDescription['class'],
			$handlerDescription['method'],
			$handlerDescription['sort'],
			'',
			$handlerDescription['arguments']
		);
	}

	/**
	 * Удаляет неиспользуемые обработчикы из базы данных
	 *
	 * @param $handlerList array список обработчиков для регистрации
	 * @param $registeredList array список ранее зарегистрированных обработчиков
	 * */
	protected static function deleteHandlers($handlerList, $registeredList): void
	{
		foreach ($registeredList as $handlerKey => $handlerRow)
		{
			if (!isset($handlerList[$handlerKey]))
			{
				static::deleteHandler($handlerRow);
			}
		}
	}

	/**
	 * Удаляет обработчик из базы данных
	 *
	 * @param $handlerRow array ранее зарегистрированный обработчик
	 * */
	protected static function deleteHandler($handlerRow): void
	{
		$eventManager = Main\EventManager::getInstance();

		$handlerArgs = $handlerRow['TO_METHOD_ARG'];

		if (is_string($handlerArgs))
		{
			$handlerArgsUnserialize = unserialize($handlerArgs, [false]);

			if (is_array($handlerArgsUnserialize) && !empty($handlerArgsUnserialize))
			{
				$handlerArgs = $handlerArgsUnserialize;
			}
		}

		$eventManager->unregisterEventHandler(
			$handlerRow['FROM_MODULE_ID'],
			$handlerRow['MESSAGE_ID'],
			Config::getModuleName(),
			$handlerRow['TO_CLASS'],
			$handlerRow['TO_METHOD'],
			'',
			$handlerArgs
		);
	}
}
