<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Injection\Engine;
use YandexPay\Pay\Reference\Concerns;

class Order extends AbstractBehavior
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return static::getMessage('TITLE', null, 'order');
	}

	public function getFields() : array
	{
		return [
			'PATH' => [
				'GROUP' => static::getMessage('GROUP_BEHAVIOR'),
				'TYPE' => 'string',
				'TITLE' => self::getMessage('PATH'),
				'MANDATORY' => 'Y',
			],
		] + parent::getFields();
	}

	public function getEngineReference() : string
	{
		return Engine\Order::class;
	}

	public function getMode() : string
	{
		return Registry::ORDER;
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