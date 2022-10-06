<?php

use YandexPay\Pay;
use Bitrix\Main;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles("install/admin", "admin");
	$updater->CopyFiles("install/js", "js/yandexpaypay");
	$updater->CopyFiles("install/components", "components/yandexpay.pay");
	$updater->CopyFiles("install/images", "images/yandexpay.pay");

	AddEventHandler('main', 'OnModuleUpdate', static function($readyModules)
	{
		if (!Main\Loader::includeModule('yandexpay.pay')) { return; }

		//table

		$controller = new Pay\Reference\Storage\Controller();
		$controller->createTable([
			Pay\Logger\Table::class,
		]);

		// agents

		$agentClasses = [
			'\\' . Pay\Logger\Cleaner::class,
		];

		/** @var class-string<Pay\Reference\Agent\Regular> $agentClass */
		foreach ($agentClasses as $agentClass)
		{
			foreach ($agentClass::getAgents() as $handler)
			{
				Pay\Reference\Agent\Controller::register(
					$agentClass,
					$handler
				);
			}
		}

	});
}