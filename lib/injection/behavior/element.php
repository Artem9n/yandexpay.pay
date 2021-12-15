<?php
namespace YandexPay\Pay\Injection\Behavior;

use Bitrix\Iblock;
use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Injection\Engine;

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
		if (!Main\Loader::includeModule('iblock')) { return []; }

		$result = [];

		$query = Iblock\IblockTable::getList([
			'filter' => [
				'=TYPE.LANG_MESSAGE.LANGUAGE_ID' => LANGUAGE_ID,
			],
			'select' => [
				'ID',
				'NAME',
				'TYPE_NAME' => 'TYPE.LANG_MESSAGE.NAME',
			],
		]);

		while ($row = $query->fetch())
		{
			$result[] = [
				'ID' => $row['ID'],
				'VALUE' => sprintf('[%s] %s', $row['ID'], $row['NAME']),
				'GROUP' => $row['TYPE_NAME'],
			];
		}

		return $result;
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
			'URL_TEMPLATE' => $this->getUrlTemplate(),
		];
	}
}