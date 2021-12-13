<?php
namespace YandexPay\Pay\Injection\Behavior;

use Bitrix\Iblock;
use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;
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
		return [
			'SELECTOR_ELEMENT' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('SELECTOR'),
				'MANDATORY' => 'Y',
			],
			'IBLOCK' => [
				'TYPE' => 'enumeration',
				'TITLE' => self::getMessage('IBLOCK'),
				'MANDATORY' => 'Y',
				'VALUES' => $this->getIblockEnum(),
			],
		];
	}

	public function getSelectorCode() : string
	{
		return 'SELECTOR_ELEMENT';
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

	public function install(int $injectionId, array $settings) : void
	{
		Assert::notNull($settings['IBLOCK'], 'settings[iblock]');

		\YandexPay\Pay\Injection\Engine\Element::register([
			'module' => 'main',
			'event' => 'onEpilog',
			'arguments' => [
				$injectionId,
				$settings,
			],
		]);
	}

	public function uninstall(int $injectionId, array $settings) : void
	{
		try
		{
			Assert::notNull($settings['IBLOCK'], 'settings[iblock]');

			\YandexPay\Pay\Injection\Engine\Element::unregister([
				'module' => 'main',
				'event' => 'onEpilog',
				'arguments' => [
					$injectionId,
					$settings,
				],
			]);
		}
		catch (Main\SystemException $exception)
		{
			//nothing
		}
	}
}