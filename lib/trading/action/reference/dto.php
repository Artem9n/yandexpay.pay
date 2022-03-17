<?php
namespace YandexPay\Pay\Trading\Action\Reference;

use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;
use YandexPay\Pay\Reference\Common\Collection;
use YandexPay\Pay\Utils;

abstract class Dto extends Model
{
	public function requireField($name)
	{
		$value = $this->getField($name);

		if ($value === null)
		{
			throw new Exceptions\DtoProperty(sprintf('%s is missing', $name));
		}

		Assert::notNull($value, $name);

		return $value;
	}

	public function getField($name)
	{
		return Utils\DotChain::get($this->fields, $name);
	}

	public function setField($name, $value) : void
	{
		throw new Main\NotSupportedException();
	}

	protected function requireChildCollection($name) : Collection
	{
		$collection = $this->getChildCollection($name);

		if ($collection === null)
		{
			throw new Exceptions\DtoProperty(sprintf('%s is missing', $name));
		}

		return $collection;
	}

	protected function requireChildModel($name) : Model
	{
		$model = $this->getChildModel($name);

		if ($model === null)
		{
			throw new Exceptions\DtoProperty(sprintf('%s is missing', $name));
		}

		return $model;
	}

	protected function loadChildCollection($fieldKey) : ?Collection
	{
		$classMap = $this->collectionMap();

		if (!isset($classMap[$fieldKey]))
		{
			throw new Main\ArgumentException(sprintf(
				'child collection for %s not mapped',
				$fieldKey
			));
		}

		$className = $classMap[$fieldKey];
		$data = $this->getField($fieldKey);

		if ($data === null) { return null; }

		return $className::initialize($data);
	}

	/**
	 * @return array<string, string> $fieldKey => $className
	 */
	protected function collectionMap() : array
	{
		return [];
	}

	protected function loadChildModel($fieldKey) : ?Model
	{
		$classMap = $this->modelMap();

		if (!isset($classMap[$fieldKey]))
		{
			throw new Main\ArgumentException(sprintf(
				'child model for %s not mapped',
				$fieldKey
			));
		}

		$className = $classMap[$fieldKey];
		$data = $this->getField($fieldKey);

		if ($data === null) { return null; }

		return new $className($data);
	}

	/**
	 * @return array<string, string> $fieldKey => $className
	 */
	protected function modelMap() : array
	{
		return [];
	}
}