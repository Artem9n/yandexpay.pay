<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Concerns;

class Basket
	implements BehaviorInterface
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return static::getMessage('TITLE', null, 'basket');
	}

	public function getFields() : array
	{
		return [
			'DUMMY' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('DUMMY'),
				'MANDATORY' => 'Y',
			],
		];
	}

	public function install()
	{

	}

	public function uninstall()
	{

	}
}