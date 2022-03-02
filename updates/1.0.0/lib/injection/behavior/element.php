<?php
namespace YandexPay\Pay\Injection\Behavior;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Engine;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;

class Element extends AbstractBehavior
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return static::getMessage('TITLE', null, 'element');
	}

	public function getFields() : array
	{
		return parent::getFields() + [
			'SELECTOR' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('SELECTOR'),
				'MANDATORY' => 'Y',
			],
			'IBLOCK' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'TITLE' => self::getMessage('IBLOCK'),
				'VALUES' => $this->getIblockEnum(),
			],
		];
	}

	public function getEngineReference() : string
	{
		return Engine\Element::class;
	}

	protected function getIblockEnum() : array
	{
		$environment = EntityRegistry::getEnvironment();

		return $environment->getCatalog()->getEnumIblock();
	}

	public function getIblock() : ?int
	{
		return $this->getValue('IBLOCK');
	}

	public function getUrlTemplate() : ?string
	{
		return $this->getValue('URL_TEMPLATE');//todo
	}

	public function getMode() : string
	{
		return Registry::ELEMENT;
	}

	protected function eventSettings() : array
	{
		return [
			'IBLOCK' => $this->getIblock(),
			//'URL_TEMPLATE' => $this->getUrlTemplate(),
		];
	}
}