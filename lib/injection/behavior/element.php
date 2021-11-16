<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Concerns;

class Element
	implements BehaviorInterface
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return static::getMessage('TITLE', null, 'element');
	}

	public function getFields() : array
	{
		return [];
	}

	public function install()
	{

	}

	public function uninstall()
	{

	}
}