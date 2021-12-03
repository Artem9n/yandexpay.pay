<?php
namespace YandexPay\Pay\Injection\Behavior;

use Bitrix\Main;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Concerns;

class Order
	implements BehaviorInterface
{
	use Concerns\HasMessage;

	public function getTitle() : string
	{
		return static::getMessage('TITLE', null, 'order');
	}

	public function getFields() : array
	{
		return [
			'SELECTOR_ORDER' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('SELECTOR'),
				'MANDATORY' => 'Y',
			],
			'PATH_ORDER' => [
				'TYPE' => 'string',
				'TITLE' => self::getMessage('PATH'),
				'MANDATORY' => 'Y',
			]
		];
	}

	public function install(int $injectionId, array $settings) : void
	{
		//Assert::notNull($settings['SELECTOR_BASKET'], 'settings[SELECTOR_BASKET]');

		\YandexPay\Pay\Injection\Engine\Order::register([
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

			\YandexPay\Pay\Injection\Engine\Order::unregister([
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