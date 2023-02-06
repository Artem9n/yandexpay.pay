<?php

use Bitrix\Main;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Ui\UseCase\AutoInstallInjection;
use YandexPay\Pay\Injection;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles("install/js", "js/yandexpaypay");
	$updater->CopyFiles("install/components", "components/yandexpay.pay");

	AddEventHandler('main', 'OnModuleUpdate', static function($readyModules)
	{
		if (!Main\Loader::includeModule('yandexpay.pay')) { return; }

		try
		{
			//get setup ids

			$tradingSetups = [];
			$querySetup = Trading\Setup\RepositoryTable::getList([
				'select' => [ 'ID', 'SITE_ID' ]
			]);

			while($setup = $querySetup->fetchObject())
			{
				$tradingSetups[$setup->getId()] = $setup->getSiteId();
			}

			if (empty($tradingSetups)) { return; }

			$environment = Trading\Entity\Registry::getEnvironment();

			//change aspro solution

			$typeSolutions = Injection\Solution\Registry::getTypes();

			$querySettings = Trading\Settings\RepositoryTable::getList([
				'filter' => [
					'SETUP_ID' => array_keys($tradingSetups),
					'%VALUE' => 'Aspro',
					'=NAME' => 'SOLUTION',
				],
				'select' => [ 'ID', 'VALUE', 'NAME', 'SETUP_ID' ],
			]);

			while ($setting = $querySettings->fetchObject())
			{
				$siteTemplates = $environment->getSite()->getTemplate($tradingSetups[$setting['SETUP_ID']]);

				if (empty($siteTemplates)) { continue; }

				foreach ($typeSolutions as $type)
				{
					$solution = Injection\Solution\Registry::getInstance($type);

					if (!$solution->isMatch(['TEMPLATES' => $siteTemplates])) { continue; }

					Trading\Settings\RepositoryTable::update(['ID' => $setting->getId(), 'NAME' => $setting->getName()], [
						'VALUE' => $solution->getType(),
					]);

					break;
				}
			}

			// change injection settings

			foreach(array_keys($tradingSetups) as $id)
			{
				$setup = Trading\Setup\Model::wakeUp(['ID' => $id]);
				$setup->fill();
				$options = $setup->wakeupOptions();
				$optionsValues = $options->getValues();

				$fields = AutoInstallInjection::getSettingsFields($setup);
				$defaultValues = AutoInstallInjection::collectDefaultSettings($fields, ['INJECTION']);

				if (mb_strpos($optionsValues['SOLUTION'], 'Aspro') === false) { continue; }

				// new behavior aspro solution

				$result['INJECTION'] = [];

				foreach ($optionsValues['INJECTION'] as $inject)
				{
					foreach ($inject as $injectId)
					{
						$result['INJECTION'][] = $injectId;
					}
				}

				$newBehaviors = [
					Injection\Behavior\Registry::BASKET_FLY,
					Injection\Behavior\Registry::ELEMENT_FAST,
				];

				foreach ($newBehaviors as $behavior)
				{
					foreach ($defaultValues['INJECTION'] as $valueDefault)
					{
						if ($valueDefault['BEHAVIOR'] !== $behavior) { continue; }

						$injection = Injection\Setup\RepositoryTable::add([
							'BEHAVIOR' => $behavior,
							'TRADING_ID' => $id,
							'TRADING' => $setup,
							'SETTINGS' => $valueDefault['SETTINGS']
						]);

						$result['INJECTION'][] = $injection->getPrimary();
					}
				}

				if (empty($result['INJECTION'])) { return; }

				$setup->syncSettings($result);
				$setup->getSettings()->save(true);
			}
		}
		catch (\Throwable $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}
	});
}