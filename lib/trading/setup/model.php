<?php

namespace YandexPay\Pay\Trading\Setup;

use YandexPay\Pay\Trading\Settings;

class Model extends EO_Repository
{
	protected $options;
	protected $isOptionsReady = false;

	public function wakeupOptions() : Settings\Options
	{
		$options = $this->getOptions();

		if ($this->isOptionsReady) { return $options; }

		$values = $this->fillSettings()->getValues();
		$options->setValues($values);
		$this->isOptionsReady = true;

		return $options;
	}

	public function getOptions() : Settings\Options
	{
		if ($this->options === null)
		{
			$this->options = new Settings\Options();
		}

		return $this->options;
	}

	public function getEnvironment()
	{
		return null; //todo
	}

	/** @noinspection PhpUnused */
	public function activateAction() : void
	{
		$this->setActive(true);
		$this->save();
	}

	/** @noinspection PhpUnused */
	public function deactivateAction() : void
	{
		$this->setActive(false);
		$this->save();
	}

	/** @noinspection PhpUnused */
	public function resetAction() : void
	{
		$this->removeAllSettings();
		$this->save();
	}

	/** @noinspection PhpUnused */
	public function deleteAction() : void
	{
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

	/**
	 * @param array<string, Model> $models
	 * @param array<string, mixed> $values
	 */
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