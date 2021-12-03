<?php

namespace YandexPay\Pay\Component\Trading\Injection;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Injection;

class Form extends Pay\Component\Model\Form
{
	/** @var Injection\Setup\Model */
	protected $injection;

	public function update($primary, array $values) : Main\ORM\Data\UpdateResult
	{
		$injection = $this->getInjection();

		$behavior = Injection\Behavior\Registry::getInstance($injection->getBehavior());
		$behavior->uninstall($primary, $injection->getSettings());

		$fields = $this->getComponentResult('FIELDS');

		$values = $this->sliceFieldsDependHidden($fields, $values);

		$update = parent::update($primary, $values);

		if ($values['ACTIVE'] && $update->isSuccess())
		{
			$behavior = Injection\Behavior\Registry::getInstance($values['BEHAVIOR']);
			$behavior->install($primary, $values['SETTINGS']);
		}

		return $update;
	}

	public function add(array $values) : Main\ORM\Data\AddResult
	{
		$add = parent::add($values);

		$fields = $this->getComponentResult('FIELDS');

		$values = $this->sliceFieldsDependHidden($fields, $values);

		if ($values['ACTIVE'] && $add->isSuccess())
		{
			$behavior = Injection\Behavior\Registry::getInstance($values['BEHAVIOR']);
			$behavior->install($add->getId(), $values['SETTINGS']);
		}

		return $add;
	}

	protected function getInjection() : Injection\Setup\Model
	{
		if ($this->injection === null)
		{
			$primary = $this->getComponentParam('PRIMARY');
			$dataClass = $this->getDataClass();

			Assert::notNull($primary, 'params[PRIMARY]');

			$this->injection = $dataClass::wakeUpObject($primary);

			Assert::typeOf($this->injection, Injection\Setup\Model::class, 'setup injection');

			$this->injection->fill();
		}

		return $this->injection;
	}
}