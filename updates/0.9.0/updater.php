<?php

use YandexPay\Pay;
use Bitrix\Main;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles('install/admin', 'admin');
	$updater->CopyFiles('install/components', 'components/yandexpay.pay');
	$updater->CopyFiles('install/js', 'js/yandexpaypay');
	$updater->CopyFiles('install/tools', 'tools/yandexpay.pay');

	AddEventHandler('main', 'OnModuleUpdate', static function($readyModules)
	{
		if (!Main\Loader::includeModule('yandexpay.pay')) { return; }

		// events

		$eventClasses = [
			Pay\Ui\Trading\Sale\PaySystemTab::class,
			Pay\Gateway\OtherHandlerMode::class,

		];

		/** @var class-string<Pay\Reference\Event\Regular> $eventClass */
		foreach ($eventClasses as $eventClass)
		{
			foreach ($eventClass::getHandlers() as $handler)
			{
				Pay\Reference\Event\Controller::register(
					$eventClass,
					$handler
				);
			}
		}

		// tables

		$controller = new Pay\Reference\Storage\Controller();
		$controller->createTable([
			Pay\Trading\Setup\RepositoryTable::class,
			Pay\Injection\Setup\RepositoryTable::class,
			Pay\Trading\Settings\RepositoryTable::class,
		]);
	});
}
