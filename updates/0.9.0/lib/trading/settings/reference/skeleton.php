<?php

namespace YandexPay\Pay\Trading\Settings\Reference;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Trading\Entity;

abstract class Skeleton
{
	protected $values;
	protected $fieldset = [];
	protected $fieldsetCollection = [];

	abstract public function getFields(Entity\Reference\Environment $environment, string $siteId) : array;

	public function validate() : Main\Result
	{
		return Pay\Result\Facade::merge(
			$this->validateSelf(),
			$this->validateFieldset(),
			$this->validateFieldsetCollection()
		);
	}

	protected function validateSelf() : Main\Result
	{
		return new Main\Result();
	}

	protected function validateFieldset() : Main\Result
	{
		$map = $this->getFieldsetMap();
		$result = new Main\Entity\Result();

		foreach ($map as $key => $dummy)
		{
			$fiedlsetValidation = $this->getFieldset($key)->validate();

			if (!isset($fiedlsetValidation)) { continue; }

			$result = Pay\Result\Facade::merge($result, $fiedlsetValidation);
		}

		return $result;
	}

	protected function validateFieldsetCollection() : Main\Result
	{
		$map = $this->getFieldsetCollectionMap();
		$result = new Main\Entity\Result();

		foreach ($map as $key => $dummy)
		{
			$fiedlsetValidation = $this->getFieldsetCollection($key)->validate();

			if (!isset($fiedlsetValidation)) { continue; }

			$result = Pay\Result\Facade::merge($result, $fiedlsetValidation);
		}

		return $result;
	}

	public function setValues(array $values) : void
	{
		$leftValues = $this->setFieldsetValues($values);
		$leftValues = $this->setFieldsetCollectionValues($leftValues);

		$this->values = $leftValues;
		$this->applyValues();
	}

	protected function setFieldsetValues(array $values) : array
	{
		$map = $this->getFieldsetMap();

		if (empty($map)) { return $values; }

		foreach ($map as $key => $dummy)
		{
			$fieldsetValues = isset($values[$key]) && is_array($values[$key])
				? $values[$key]
				: [];

			$this->getFieldset($key)->setValues($fieldsetValues);
		}

		return array_diff_key($values, $map);
	}

	protected function setFieldsetCollectionValues(array $values) : array
	{
		$map = $this->getFieldsetCollectionMap();

		if (empty($map)) { return $values; }

		foreach ($map as $key => $dummy)
		{
			$fieldsetValues = isset($values[$key]) && is_array($values[$key])
				? $values[$key]
				: [];

			$this->getFieldsetCollection($key)->setValues($fieldsetValues);
		}

		return array_diff_key($values, $map);
	}

	protected function applyValues() : void
	{
		// nothing by default
	}

	public function getValue($key, $default = null)
	{
		return $this->values[$key] ?? $default;
	}

	public function requireValue($key, $default = null)
	{
		$result = $this->getValue($key, $default);

		if (Pay\Utils\Value::isEmpty($result))
		{
			throw new Main\SystemException('Required option ' . $key . ' not set');
		}

		return $result;
	}

	/** @noinspection AdditionOperationOnArraysInspection */
	public function getValues() : array
	{
		$result = $this->values;
		$result += $this->getFieldsetValues();
		$result += $this->getFieldsetCollectionValues();

		return $result;
	}

	protected function getFieldsetValues() : array
	{
		$result = [];

		foreach ($this->getFieldsetMap() as $key => $dummy)
		{
			$result[$key] = $this->getFieldset($key)->getValues();
		}

		return $result;
	}

	protected function getFieldsetCollectionValues() : array
	{
		$result = [];

		foreach ($this->getFieldsetCollectionMap() as $key => $dummy)
		{
			$result[$key] = $this->getFieldsetCollection($key)->getValues();
		}

		return $result;
	}

	/** @return array<string, Fieldset> */
	protected function getFieldsetMap() : array
	{
		return [];
	}

	protected function getFieldset($key) : Fieldset
	{
		if (!isset($this->fieldset[$key]))
		{
			$this->fieldset[$key] = $this->createFieldset($key);
		}

		return $this->fieldset[$key];
	}

	protected function createFieldset($key) : Fieldset
	{
		$classMap = $this->getFieldsetMap();

		if (!isset($classMap[$key]))
		{
			throw new Main\ArgumentException(sprintf('Fieldset %s not defined', $key));
		}

		$className = $classMap[$key];

		return new $className();
	}

	/** @return array<string, FieldsetCollection> */
	protected function getFieldsetCollectionMap() : array
	{
		return [];
	}

	protected function getFieldsetCollection(string $key) : FieldsetCollection
	{
		if (!isset($this->fieldsetCollection[$key]))
		{
			$this->fieldsetCollection[$key] = $this->createFieldsetCollection($key);
		}

		return $this->fieldsetCollection[$key];
	}

	protected function createFieldsetCollection(string $key) : FieldsetCollection
	{
		$classMap = $this->getFieldsetCollectionMap();

		if (!isset($classMap[$key]))
		{
			throw new Main\ArgumentException(sprintf('Fieldset collection %s not defined', $key));
		}

		$className = $classMap[$key];

		return new $className();
	}
}