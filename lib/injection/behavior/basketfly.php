<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Engine;

class BasketFly extends Basket
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
					'HELP' => self::getMessage('PATH_HELP'),
					'MANDATORY' => 'Y',
					'SETTINGS' => [
						'ROWS' => 5,
						'SIZE' => 25,
					],
				],
			] + parent::getFields();
	}

	public function getEngineReference() : string
	{
		return Engine\BasketFly::class;
	}

	protected function events() : array
	{
		return [
			['main', 'onEndBufferContent'],
		];
	}
}