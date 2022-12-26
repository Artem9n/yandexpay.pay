<?php

namespace YandexPay\Pay\Trading\Setup;

use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings;

class Model extends EO_Repository
{
	protected $options;
	protected $environment;
	protected $isOptionsReady = false;

	public function install() : void
	{
		$siteId = $this->getSiteId();
		$environment = $this->getEnvironment();

		$environment->getRoute()->installPublic($siteId);
		$environment->getPlatform()->install();
		$this->clearComposite();
	}

	public function wakeupOptions() : Settings\Options
	{
		$options = $this->getOptions();

		if ($this->isOptionsReady) { return $options; }

		$values = $this->fillSettings()->getValues();
		/** @noinspection AdditionOperationOnArraysInspection */
		$values += $this->getOptionDefaults($values);
		$options->setValues($values);
		$this->isOptionsReady = true;

		return $options;
	}

	protected function clearComposite() : void
	{
		$this->fillSiteId();
		$siteId = $this->getSiteId();
		$environment = $this->getEnvironment();
		$domain = $environment->getSite()->getDomain($siteId);
		$environment->getCompositeCache()->clearCache($domain);
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

	protected function getOptionDefaults(array $values = []) : array
	{
		$result = [
			'PERSON_TYPE_ID' => $this->getPersonTypeId()
		];

		if (!empty($values['PAYSYSTEM_CARD']))
		{
			$result['PAYSYSTEM_SPLIT'] = $values['PAYSYSTEM_CARD'];
		}

		return $result;
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
		$this->clearComposite();
		$this->setActive(true);
		$this->fillInjection()->activate();
		$this->save();
	}

	public function deactivateAction() : void
	{
		$this->clearComposite();
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
		$this->clearComposite();
		$this->fillInjection()->delete();
		$this->delete();
	}

	public function syncSettings(array $values) : void
	{
		$map = $this->fillSettings()->mapCollection();
		$add = array_diff_key($values, $map);
		$update = array_intersect_key($values, $map);
		$delete = array_diff_key($map, $values);

		$this->checkChangeUserGroup($map, $update);
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

	protected function checkChangeUserGroup(array $models, array $updates) : void
	{
		/** @var Settings\Model|null $userGroupModel */
		$userGroupModel = $models['USER_GROUPS'] ?? null;

		if ($userGroupModel === null) { return; }

		$beforeValue = (int)$userGroupModel->getValue();
		$afterValue = (int)$updates['USER_GROUPS'];

		if ($beforeValue !== $afterValue)
		{
			$this->clearComposite();
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