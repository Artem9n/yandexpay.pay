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
		$this->getInjection()->unregister();

		$update = parent::update($primary, $values);

		if ($values['ACTIVE'] && $update->isSuccess())
		{
			$this->getInjection()->register();
		}

		return $update;
	}

	public function add(array $values) : Main\ORM\Data\AddResult
	{
		$add = parent::add($values);

		if ($values['ACTIVE'] && $add->isSuccess())
		{
			$this->getInjection()->register();
		}

		return $add;
	}

	protected function getInjection() : Injection\Setup\Model
	{
		$primary = $this->getComponentParam('PRIMARY');
		$dataClass = $this->getDataClass();

		Assert::notNull($primary, 'params[PRIMARY]');

		$this->injection = $dataClass::wakeUpObject($primary);

		Assert::typeOf($this->injection, Injection\Setup\Model::class, 'setup injection');

		return $this->injection;
	}
}