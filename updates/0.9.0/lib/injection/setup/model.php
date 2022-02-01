<?php

namespace YandexPay\Pay\Injection\Setup;

use YandexPay\Pay\Injection;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings;
use YandexPay\Pay\Trading;

class Model extends EO_Repository
{
	protected $options;

	public function activateAction() : void
	{
		$this->register();
		$this->save();
	}

	public function deactivateAction() : void
	{
		$this->unregister();
		$this->save();
	}

	public function deleteAction() : void
	{
		$this->unregister();
		$this->delete();
	}

	public function register() : void
	{
		$this->wakeupOptions()->install($this->getId());
	}

	public function unregister() : void
	{
		$this->wakeupOptions()->uninstall($this->getId());
	}

	public function wakeupOptions() : Injection\Behavior\BehaviorInterface
	{
		$options = $this->getOptions();
		$options->setValues($this->collectSettings());

		return $options;
	}

	protected function collectSettings() : array
	{
		$type = $this->getBehavior();
		$prefix = mb_strtoupper($type) . '_';
		$prefixLength = mb_strlen($prefix);
		$result = [];

		foreach ((array)$this->getSettings() as $name => $value)
		{
			if (mb_strpos($name, $prefix) !== 0) { continue; }

			$optionName = mb_substr($name, $prefixLength);

			$result[$optionName] = $value;
		}

		return $result;
	}

	public function getOptions() : Injection\Behavior\BehaviorInterface
	{
		if ($this->options === null)
		{
			$this->options = $this->createOptions();
		}

		return $this->options;
	}

	protected function createOptions() : Injection\Behavior\BehaviorInterface
	{
		$this->fill();

		$behavior = $this->getBehavior();

		Assert::notNull($behavior, 'behavior');

		return Injection\Behavior\Registry::getInstance($behavior);
	}
}