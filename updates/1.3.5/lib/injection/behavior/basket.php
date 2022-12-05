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
		return [
			'PATH' => [
				'GROUP' => self::getMessage('GROUP_BEHAVIOR'),
				'TYPE' => 'string',
				'TITLE' => self::getMessage('PATH'),
				'MANDATORY' => 'Y',
			],
		] + parent::getFields();
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
			'PATH' => $this->getPath(),
			'SITE_ID' => $this->getSiteId(),
		];
	}
}