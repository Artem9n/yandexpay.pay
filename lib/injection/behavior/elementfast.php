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
				'QUERY_CHECK_PARAMS' => [
					'TYPE' => 'string',
					'TITLE' => self::getMessage('FIELD_QUERY_CHECK_PARAMS'),
					'GROUP' => self::getMessage('GROUP_AJAX_PARAMS'),
					'MANDATORY' => 'Y',
				],
				'QUERY_ELEMENT_ID_PARAM' => [
					'TYPE' => 'string',
					'TITLE' => self::getMessage('FIELD_QUERY_ELEMENT_ID_PARAM'),
					'GROUP' => self::getMessage('GROUP_AJAX_PARAMS'),
				],
		];
	}

	public function getEngineReference() : string
	{
		return Engine\ElementFast::class;
	}

	public function getMode() : string
	{
		return Registry::ELEMENT;
	}

	protected function getQueryParam() : string
	{
		return $this->requireValue('QUERY_PARAM');
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