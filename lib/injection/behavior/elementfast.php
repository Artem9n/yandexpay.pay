<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Engine;

class ElementFast extends Element
{
	use Concerns\HasMessage;

	public function getFields() : array
	{
		return parent::getFields() + [
			'QUERY_PARAM' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('QUERY_PARAM'),
				'GROUP' => self::getMessage('VIEW'),
				'MANDATORY' => 'Y'
			],
		];
	}

	public function getEngineReference() : string
	{
		return Engine\ElementFast::class;
	}

	public function getMode() : string
	{
		return Registry::ELEMENT_FAST;
	}

	protected function getQueryParam() : string
	{
		return $this->requireValue('QUERY_PARAM');
	}

	protected function eventSettings() : array
	{
		return [
			'IBLOCK' => $this->getIblock(),
			'QUERY_PARAM' => $this->getQueryParam(),
		];
	}

	protected function events() : array
	{
		return [
			['main', 'onEndBufferContent'],
		];
	}
}