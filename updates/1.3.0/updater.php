<?php

use YandexPay\Pay;
use Bitrix\Main;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles('install/js', 'js/yandexpaypay');
	$updater->CopyFiles('install/components', 'components/yandexpay.pay');

	AddEventHandler('main', 'OnModuleUpdate', static function($readyModules)
	{
		if (!Main\Loader::includeModule('yandexpay.pay')) { return; }

		// platform

		$environment = Pay\Trading\Entity\Registry::getEnvironment();
		$environment->getPlatform()->install();

		// events

		$eventClasses = [
			'\\' . Pay\Trading\UseCase\Restrictions\Event::class,
			'\\' . Pay\Ui\UserField\Publisher::class,
			'\\' . Pay\Delivery\Event::class,
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

		// agents

		$agentClasses = [
			'\\' . Pay\Ui\Update\Check\Agent::class,
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

		// tables

		$controller = new Pay\Reference\Storage\Controller();
		$controller->createTable([
			Pay\Delivery\Yandex\Internals\RepositoryTable::class,
		]);

		//delivery

		Pay\Ui\UseCase\AutoInstallDelivery::install();
	});
}
?>