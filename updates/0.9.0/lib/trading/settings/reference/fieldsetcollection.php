<?php

namespace YandexPay\Pay\Trading\Settings\Reference;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Trading\Entity;

/** @method Fieldset current() */
abstract class FieldsetCollection
	implements \ArrayAccess, \Countable, \IteratorAggregate
{
	use Pay\Reference\Concerns\HasCollection;

	protected $configurationItem;

	/** @return Fieldset */
	abstract public function getItemReference() : string;

	public function validate() : Main\Result
	{
		$result = new Main\Result();

		foreach ($this->collection as $model)
		{
			$modelResult = $model->validate();

			if ($modelResult->isSuccess()) { continue; }

			$result->addErrors($modelResult->getErrors());
		}

		return $result;
	}

	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return
			[ 'MULTIPLE' => 'Y' ]
			+ $this->getConfigurationItem()->getFieldDescription($environment, $siteId);
	}

	public function getFields($environment, string $siteId) : array
	{
		return $this->getConfigurationItem()->getFields($environment, $siteId);
	}

	public function setValues(array $values) : void
	{
		$this->collection = [];

		foreach ($values as $fieldsetValues)
		{
			$item = $this->createItem();
			$item->setValues($fieldsetValues);

			$this->collection[] = $item;
		}
	}

	public function getValues() : array
	{
		$result = [];

		foreach ($this->collection as $fieldset)
		{
			$result[] = $fieldset->getValues();
		}

		return $result;
	}

	protected function createItem()
	{
		$itemReference = $this->getItemReference();

		return new $itemReference();
	}

	protected function getConfigurationItem()
	{
		if ($this->configurationItem !== null)
		{
			$result = $this->configurationItem;
		}
		else if (!empty($this->collection))
		{
			$result = reset($this->collection);
		}
		else
		{
			$result = $this->createItem();
			$this->configurationItem = $result;
		}

		return $result;
	}
}