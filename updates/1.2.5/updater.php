<?php

use Bitrix\Main\Application;
use YandexPay\Pay;
use Bitrix\Main;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles("install/js", "js/yandexpaypay");
	$updater->CopyFiles("install/components", "components/yandexpay.pay");

	AddEventHandler('main', 'OnModuleUpdate', static function($readyModules)
	{
		$querySetup = Pay\Trading\Setup\RepositoryTable::getList([]);

		while ($item = $querySetup->fetchObject())
		{
			$item->install();
		}

		Pay\Injection\Migration::reinstallEvents();
	});
}
?>