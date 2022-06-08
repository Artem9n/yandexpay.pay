<?php

use YandexPay\Pay;
use Bitrix\Main;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles('install/components', 'components/yandexpay.pay');
	$updater->CopyFiles('install/js', 'js/yandexpaypay');
	$updater->CopyFiles('install/services', 'services/yandexpay.pay');

	AddEventHandler('main', 'OnModuleUpdate', static function($readyModules)
	{
		if (!Main\Loader::includeModule('yandexpay.pay')) { return; }

		// events

		$eventClasses = [
			Pay\Trading\Action\Rest\OrderStatus\Action::class,
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

		// statuses

		Pay\Trading\Entity\Sale\Status::install();
	});
}
