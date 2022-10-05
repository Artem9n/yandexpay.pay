<?php

namespace YandexPay\Pay\Reference\Storage;

use Bitrix\Main\DB\Connection;
use Bitrix\Main;
use YandexPay\Pay\Reference\Storage;

class Installer
{
	protected $entity;

	public function __construct(Main\Entity\Base $entity)
	{
		$this->entity = $entity;
	}

	public function install() : void
	{
		$this->createTable();
		$this->createIndexes();
		$this->alterArrayText();
		$this->alterLongText();
	}

	protected function createTable() : void
	{
		$this->entity->createDbTable();
	}

	protected function alterArrayText() : void
	{
		foreach ($this->entity->getFields() as $field)
		{
			if (!($field instanceof Main\ORM\Fields\ArrayField)) { continue; }

			$connection = $this->entity->getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$tableName = $this->entity->getDBTableName();
			$columnName = $field->getColumnName();

			$connection->queryExecute(sprintf(
				'ALTER TABLE %s MODIFY COLUMN %s text',
				$sqlHelper->quote($tableName),
				$sqlHelper->quote($columnName)
			));
		}
	}

	protected function alterLongText() : void
	{
		foreach ($this->entity->getFields() as $field)
		{
			if (!($field instanceof Storage\Field\LongTextField)) { continue; }

			$connection = $this->entity->getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$tableName = $this->entity->getDBTableName();
			$columnName = $field->getColumnName();

			$connection->queryExecute(sprintf(
				'ALTER TABLE %s MODIFY COLUMN %s longtext',
				$sqlHelper->quote($tableName),
				$sqlHelper->quote($columnName)
			));
		}
	}

	protected function createIndexes() : void
	{
		$className = $this->entity->getDataClass();
		$connection = $this->entity->getConnection();
		$tableName = $this->entity->getDBTableName();

		if (!method_exists($className, 'getTableIndexes')) { return; }

		if (empty($className::getTableIndexes())) { return; }

		foreach ($className::getTableIndexes() as $index => $fields)
		{
			$name = 'IX_' . $tableName . '_' . $index;

			if ($connection->isIndexExists($tableName, $fields)) { continue; }

			$connection->createIndex($tableName, $name, $fields);
		}
	}
}