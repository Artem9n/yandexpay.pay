<?php

use Bitrix\Main;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Ui\UseCase\AutoInstallInjection;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles("install/js", "js/yandexpaypay");
	$updater->CopyFiles("install/components", "components/yandexpay.pay");

	AddEventHandler('main', 'OnModuleUpdate', static function($readyModules)
	{
		if (!Main\Loader::includeModule('yandexpay.pay')) { return; }

		//get setup ids

		$setupIds = [];
		$querySetup = Trading\Setup\RepositoryTable::getList([
			'select' => [ 'ID' ]
		]);

		while($setup = $querySetup->fetchObject())
		{
			$setupIds[] = $setup->getId();
		}

		if (empty($setupIds)) { return; }

		//set split pay system, default = pay system card

		foreach($setupIds as $id)
		{
			$setup = Trading\Setup\Model::wakeUp(['ID' => $id]);
			$setup->fill();

			$fields = AutoInstallInjection::getSettingsFields($setup);
			$defaultValues = AutoInstallInjection::collectDefaultSettings($fields, ['PAYSYSTEM_SPLIT']);

			$setup->syncSettings($defaultValues);
			$setup->getSettings()->save(true);
		}

	});

}