<?php

namespace YandexPay\Pay\Injection\Setup;

use YandexPay\Pay\Injection;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings;
use YandexPay\Pay\Trading;

class Model extends EO_Repository
{
	public function activateAction() : void
	{
		$this->register();
		$this->setActive(true);
		$this->save();
	}

	public function deactivateAction() : void
	{
		$this->unregister();
		$this->setActive(false);
		$this->save();
	}

	public function deleteAction() : void
	{
		$this->unregister();
		$this->delete();
	}

	public function register() : void
	{
		$behavior = $this->getBehaviorModel();
		$behavior->install($this->getId(), $this->getSettings());
	}

	public function unregister() : void
	{
		$behavior = $this->getBehaviorModel();
		$behavior->uninstall($this->getId(), $this->getSettings());
	}

	public function getBehaviorModel() : Injection\Behavior\BehaviorInterface
	{
		$this->fill();
		return Injection\Behavior\Registry::getInstance($this->getBehavior());
	}

	public function getSelectorValue() : ?string
	{
		$behavior = $this->getBehaviorModel();
		$selectorCode = $behavior->getSelectorCode();
		return $this->getSettings()[$selectorCode] ?? null;
	}
}