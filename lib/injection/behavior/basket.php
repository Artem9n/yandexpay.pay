<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Engine;

class Basket extends AbstractBehavior
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return static::getMessage('TITLE', null, 'basket');
	}

	public function getFields() : array
	{
		return parent::getFields() + [
			'SELECTOR' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('SELECTOR'),
				'MANDATORY' => 'Y',
			],
			'PATH' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('PATH'),
				'MANDATORY' => 'Y',
			],
		];
	}

	public function getEngineReference() : string
	{
		return Engine\Basket::class;
	}

	public function getMode() : string
	{
		return Registry::BASKET;
	}

	public function getPath() : string
	{
		return $this->requireValue('PATH');
	}

	protected function eventSettings() : array
	{
		return [
			'PATH' => $this->getPath()
		];
	}
}