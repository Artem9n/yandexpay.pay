<?php

namespace YandexPay\Pay\Trading\Setup;

use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings;

class Model extends EO_Repository
{
	protected $options;
	protected $environment;
	protected $isOptionsReady = false;

	public function wakeupOptions() : Settings\Options
	{
		$options = $this->getOptions();

		if ($this->isOptionsReady) { return $options; }

		$values = $this->fillSettings()->getValues();
		/** @noinspection AdditionOperationOnArraysInspection */
		$values += $this->getOptionDefaults();
		$options->setValues($values);
		$this->isOptionsReady = true;

		return $options;
	}

	public function getOptions() : Settings\Options
	{
		if ($this->options === null)
		{
			$this->options = new Settings\Options();
			$this->options->setValues($this->getOptionDefaults());
		}

		return $this->options;
	}

	protected function getOptionDefaults() : array
	{
		return [
			'PERSON_TYPE_ID' => $this->getPersonTypeId()
		];
	}

	public function getEnvironment() : Entity\Reference\Environment
	{
		if ($this->environment === null)
		{
			$this->environment = Entity\Registry::getEnvironment();
		}

		return $this->environment;
	}

	public function activateAction() : void
	{
		$this->setActive(true);
		$this->fillInjection()->activate();
		$this->save();
	}

	public function deactivateAction() : void
	{
		$this->setActive(false);
		$this->fillInjection()->deactivate();
		$this->save();
	}

	public function resetAction() : void
	{
		$this->removeAllSettings();
		$this->save();
	}

	public function deleteAction() : void
	{
		$this->fillInjection()->delete();
		$this->delete();
	}

	public function syncSettings(array $values) : void
	{
		$map = $this->fillSettings()->mapCollection();
		$add = array_diff_key($values, $map);
		$update = array_intersect_key($values, $map);
		$delete = array_diff_key($map, $values);

		$this->applySettingsAdd($add);
		$this->applySettingsUpdate($map, $update);
		$this->applySettingsDelete($delete);
	}

	protected function applySettingsAdd(array $values) : void
	{
		foreach ($values as $name => $value)
		{
			$this->addToSettings(new Settings\Model([
				'NAME' => $name,
				'VALUE' => $value,
			]));
		}
	}

	protected function applySettingsUpdate(array $models, array $values) : void
	{
		foreach ($values as $name => $value)
		{
			$models[$name]->setValue($value);
		}
	}

	protected function applySettingsDelete(array $models) : void
	{
		foreach ($models as $model)
		{
			$this->removeFromSettings($model);
		}
	}
}