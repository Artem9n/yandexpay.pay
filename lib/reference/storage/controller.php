<?php

namespace YandexPay\Pay\Reference\Storage;

use Bitrix\Main;
use YandexPay\Pay\Config;

class Controller
{
	public const PREFIX = 'yapay_';

	protected $namespace;
	protected $directory;

	public function __construct(string $namespace = null, string $directory = null)
	{
		$this->namespace = $namespace ?? Config::getNamespace();
		$this->directory = $directory ?? Config::getModulePath();
	}

	public function createTable(array $classList = null) : void
	{
		if ($classList === null)
		{
			$classList = $this->getClassList(Main\Entity\DataManager::class);
		}

		/** @var Main\Entity\DataManager $className */
		foreach ($classList as $className)
		{
			$entity = $className::getEntity();

			$this->createOneTable($entity);
		}
	}

	public function dropTable(array $classList = null) : void
	{
		if ($classList === null)
		{
			$classList = $this->getClassList(Main\Entity\DataManager::class);
		}

		/** @var Main\Entity\DataManager $className */
		foreach ($classList as $className)
		{
			$entity = $className::getEntity();

			$this->dropOneTable($entity);
		}
	}

	protected function getClassList($baseClassName) : array
	{
		$baseDir = $this->directory;
		$baseNamespace = $this->namespace;
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
				$namespace = $baseNamespace . str_replace('/', '\\', $relativePath) . '\\';
				$className = $entry->getBasename('.php');

				if (!preg_match('/table$/i', $className))
				{
					$className .= 'Table';
				}

				$fullClassName = $namespace . $className;

				if (
					class_exists($fullClassName)
					&& is_subclass_of($fullClassName, $baseClassName)
				)
				{
					$result[] = $fullClassName;
				}
			}
		}

		return $result;
	}

	protected function createOneTable(Main\Entity\Base $entity) : void
	{
		$connection = $entity->getConnection();
		$tableName = $entity->getDBTableName();
		$installer = new Installer($entity);

		$this->assertTableName($tableName);

		if (!$connection->isTableExists($tableName))
		{
			$installer->install();
		}
	}

	protected function dropOneTable(Main\Entity\Base $entity) : void
	{
		$connection = $entity->getConnection();
		$tableName = $entity->getDBTableName();

		$this->assertTableName($tableName);

		if ($connection->isTableExists($tableName))
		{
			$connection->dropTable($tableName);
		}
	}

	protected function assertTableName(string $tableName) : void
	{
		if (mb_strpos($tableName, static::PREFIX) !== 0)
		{
			throw new Main\SystemException(sprintf(
				'module table %s must starts with %s',
				$tableName,
				static::PREFIX
			));
		}
	}
}
