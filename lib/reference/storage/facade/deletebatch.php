<?php

namespace YandexPay\Pay\Reference\Storage\Facade;

use Bitrix\Main;

class DeleteBatch
{
	protected $dataManager;

	/** @param class-string<Main\ORM\Data\DataManager> $dataManager */
	public function __construct(string $dataManager)
	{
		$this->dataManager = $dataManager;
	}

	public function run(array $parameters = []) : void
	{
		$dataClass = $this->dataClass();
		$entity = $dataClass::getEntity();
		$connection = $entity->getConnection();
		$helper = $connection->getSqlHelper();

		$selectQuery = $this->makeQuery($parameters);

		$connection->queryExecute(sprintf(
			'DELETE %s %s',
			$helper->quote($selectQuery->getInitAlias()),
			$this->parseSqlFrom($selectQuery->getQuery())
		));
	}

	protected function makeQuery(array $parameters) : Main\ORM\Query\Query
	{
		$dataClass = $this->dataClass();
		$query = $dataClass::query();

		foreach ($parameters as $name => $value)
		{
			if ($name === 'filter')
			{
				$query->setFilter($value);
			}
			else if ($name === 'runtime')
			{
				foreach ($value as $runtimeName => $runtime)
				{
					$query->registerRuntimeField($runtimeName, $runtime);
				}
			}
			else
			{
				throw new Main\ArgumentException(sprintf('Unknown parameter: %s', $name));
			}
		}

		return $query;
	}

	protected function parseSqlFrom(string $sql) : string
	{
		if (!preg_match('/^SELECT\s.*?\s(FROM\s.*)$/si', $sql, $matches))
		{
			throw new Main\SystemException('cant parse sql select from part');
		}

		return $matches[1];
	}

	/**
	 * for ide support
	 *
	 * @return Main\ORM\Data\DataManager
	 * @noinspection PhpDocSignatureInspection
	 */
	protected function dataClass() : string
	{
		return $this->dataManager;
	}
}