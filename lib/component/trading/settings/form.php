<?php

namespace YandexPay\Pay\Component\Trading\Settings;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Reference\Assert;

class Form extends Pay\Component\Plain\Form
{
	protected $setup;

	public function prepareComponentParams(array $params) : array
	{
		$setup = $this->getSetup();
		$options = $setup->getOptions();

		$params['FIELDS'] = $options->getFields($setup->getEnvironment(), $setup->getSiteId());
		$params['TABS'] = $this->getSetup()->getOptions()->getTabs();

		return $params;
	}

	public function getRequiredParams() : array
	{
		return [
			'DATA_CLASS_NAME',
		];
	}

	public function processPostAction(string $action, array $data) : void
	{
		$dataClass = $this->getDataClass();
		$primary = $data['PRIMARY'] ?? null;

		Assert::notNull($primary, 'data[PRIMARY]');

		$model = $dataClass::wakeUpObject($primary);
		$method = $action . 'Action';

		Assert::methodExists($model, $method);

		$model->{$method}();
	}

	public function validate(array $data, array $fields = null) : Main\Result
	{
		/** @var Pay\Trading\Setup\Model $model */
		$result = parent::validate($data, $fields);

		if (!$result->isSuccess()) { return $result; }

		$dataClass = $this->getDataClass();
		$model = $dataClass::createObject(false);
		$options = $model->getOptions();
		$options->setValues($data);

		return $options->validate();
	}

	public function load($primary, array $select = [], bool $isCopy = false) : array
	{
		$result = $this->loadSetupSettings($primary);

		if (empty($result))
		{
			$result = $this->loadFieldsDefaults($select);
		}
		else
		{
			$result = $this->fillFieldsValueEmpty($result, $select);
		}

		return $result;
	}

	protected function loadSetupSettings($primary) : array
	{
		/** @var Pay\Trading\Setup\Model $model */
		$dataClass = $this->getDataClass();
		$model = $dataClass::wakeUpObject($primary);
		$settings = $model->fillSettings();

		if (count($settings) === 0) { return []; }

		return $model->wakeupOptions()->getValues();
	}

	protected function loadFieldsDefaults(array $select = []) : array
	{
		$result = [];

		foreach ($this->getFields($select) as $fieldName => $field)
		{
			if (!isset($field['SETTINGS']['DEFAULT_VALUE'])) { continue; }

			Pay\Utils\BracketChain::set($result, $fieldName, $field['SETTINGS']['DEFAULT_VALUE']);
		}

		return $result;
	}

	protected function fillFieldsValueEmpty(array $values, array $select = []) : array
	{
		foreach ($this->getFields($select) as $fieldName => $field)
		{
			if (!empty($field['SETTINGS']['READONLY'])) { continue; }
			if (!isset($field['SETTINGS']['DEFAULT_VALUE'])) { continue; }

			$currentValue = Pay\Utils\BracketChain::get($values, $fieldName);

			if ($currentValue !== null) { continue; }

			Pay\Utils\BracketChain::set($values, $fieldName, $field['SETTINGS']['DEFAULT_VALUE']);
		}

		return $values;
	}

	public function add(array $values) : Main\Entity\Result
	{
		throw new Main\NotSupportedException();
	}

	public function update($primary, array $values) : Main\Entity\Result
	{
		$setup = $this->getSetup();

		if (!empty($values))
		{
			$fields = $this->getAllFields();

			$values = $this->applyUserFieldsOnBeforeSave($fields, $values);
			$values = $this->sliceFieldsDependHidden($fields, $values);
			$values = $this->sliceEmptyValues($values);

			$setup->syncSettings($values);
		}
		else
		{
			$setup->removeAllSettings();
		}

		return $setup->save();
	}

	protected function sliceEmptyValues(array $values) : array
	{
		foreach ($values as $name => $value)
		{
			if (Pay\Utils\Value::isEmpty($value))
			{
				unset($values[$name]);
			}
		}

		return $values;
	}

	protected function getSetup() : Pay\Trading\Setup\Model
	{
		if ($this->setup === null)
		{
			$primary = $this->getComponentParam('PRIMARY');
			$dataClass = $this->getDataClass();

			Assert::notNull($primary, 'params[PRIMARY]');

			$this->setup = $dataClass::wakeUpObject($primary);

			Assert::typeOf($this->setup, Pay\Trading\Setup\Model::class, 'setup');

			$this->setup->fill();
		}

		return $this->setup;
	}

	/** @return Main\ORM\Data\DataManager  */
	protected function getDataClass() : string
	{
		return $this->getComponentParam('DATA_CLASS_NAME');
	}
}