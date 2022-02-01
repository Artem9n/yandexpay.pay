<?php

namespace YandexPay\Pay\Reference\Storage;

use Bitrix\Main\DB\Connection;
use Bitrix\Main;

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
		$this->alterArrayText();
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
}