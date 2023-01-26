<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Engine;

class ElementFast extends Element
{
	use Concerns\HasMessage;

	public function getFields() : array
	{
		return [
			'QUERY_CHECK_PARAMS' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('FIELD_QUERY_CHECK_PARAMS'),
				'GROUP' => static::getMessage('GROUP_BEHAVIOR'),
				'MANDATORY' => 'Y',
			],
			'QUERY_ELEMENT_ID_PARAM' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('FIELD_QUERY_ELEMENT_ID_PARAM'),
				'GROUP' => static::getMessage('GROUP_BEHAVIOR'),
			],
		] + parent::getFields();
	}

	public function getEngineReference() : string
	{
		return Engine\ElementFast::class;
	}

	protected function eventSettings() : array
	{
		return [
			'IBLOCK' => $this->getIblock(),
			'SITE_ID' => $this->getSiteId(),
			'QUERY_CHECK_PARAMS' => $this->getValue('QUERY_CHECK_PARAMS'),
			'QUERY_ELEMENT_ID_PARAM' => $this->getValue('QUERY_ELEMENT_ID_PARAM')
		];
	}

	protected function events() : array
	{
		return [
			['main', 'onProlog'],
			['main', 'onEndBufferContent'],
		];
	}
}