<?php
namespace YandexPay\Pay\Trading\Action\Reference;

use Bitrix\Main\ArgumentException;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Common\Model;
use YandexPay\Pay\Reference\Common\Collection;
use YandexPay\Pay\Utils;

abstract class Skeleton extends Model
{
	public function requireField($name)
	{
		$value = $this->getField($name);

		Assert::notNull($value, $name);

		return $value;
	}

	public function getField($name)
	{
		return Utils\DotChain::get($this->fields, $name);
	}

	protected function loadChildCollection($fieldKey) : Collection
	{
		$classMap = $this->collectionMap();

		if (!isset($classMap[$fieldKey]))
		{
			throw new ArgumentException(sprintf(
				'child collection for %s not mapped',
				$fieldKey
			));
		}

		$className = $classMap[$fieldKey];
		$data = $this->requireField($fieldKey);

		return $className::initialize($data);
	}

	/**
	 * @return array<string, string> $fieldKey => $className
	 */
	protected function collectionMap() : array
	{
		return [];
	}

	protected function loadChildModel($fieldKey) : Model
	{
		$classMap = $this->modelMap();

		if (!isset($classMap[$fieldKey]))
		{
			throw new ArgumentException(sprintf(
				'child model for %s not mapped',
				$fieldKey
			));
		}

		$className = $classMap[$fieldKey];
		$data = $this->requireField($fieldKey);

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