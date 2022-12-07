<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Engine;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;

class ElementFast extends Element
{
	use Concerns\HasMessage;

	public function getFields() : array
	{
		return parent::getFields() + [
			'QUERY' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('QUERY'),
				'MANDATORY' => 'Y',
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
		return $this->getValue('QUERY');
	}

	protected function eventSettings() : array
	{
		return [
			'IBLOCK' => $this->getIblock(),
			'QUERY' => $this->getQueryParam(),
		];
	}

	protected function events() : array
	{
		return [
			['main', 'onEndBufferContent'],
		];
	}
}