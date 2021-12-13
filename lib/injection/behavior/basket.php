<?php
namespace YandexPay\Pay\Injection\Behavior;

use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;
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
			'SELECTOR_BASKET' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('SELECTOR'),
				'MANDATORY' => 'Y',
			],
			'PATH_BASKET' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('PATH'),
				'MANDATORY' => 'Y',
			]
		];
	}

	public function getSelectorCode() : string
	{
		return 'SELECTOR_BASKET';
	}

	public function install(int $injectionId, array $settings) : void
	{
		//Assert::notNull($settings['SELECTOR_BASKET'], 'settings[SELECTOR_BASKET]');

		\YandexPay\Pay\Injection\Engine\Basket::register([
			'module' => 'main',
			'event' => 'onEpilog',
			'arguments' => [
				$injectionId,
				$settings,
			],
		]);
	}

	public function uninstall(int $injectionId, array $settings)
	{
		try
		{
			//Assert::notNull($settings['IBLOCK'], 'settings[iblock]');

			\YandexPay\Pay\Injection\Engine\Basket::unregister([
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