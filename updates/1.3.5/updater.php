<?php

use Bitrix\Main;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Ui\UseCase\AutoInstallInjection;
use YandexPay\Pay\Injection;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
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

		//change aspro solution

		$querySettings = Trading\Settings\RepositoryTable::getList([
			'filter' => [
				'SETUP_ID' => $setupIds,
				'%VALUE' => 'Aspro',
				'=NAME' => 'SOLUTION',
			],
			'select' => [ 'ID', 'VALUE', 'NAME' ],
		]);

		while ($setting = $querySettings->fetchObject())
		{
			Trading\Settings\RepositoryTable::update(['ID' => $setting->getId(), 'NAME' => $setting->getName()], [
				'VALUE' => 'Aspro',
			]);
		}

		// get injection settings

		$queryInject = Injection\Setup\RepositoryTable::getList([]);

		$injections = [];

		while ($inject = $queryInject->fetchObject())
		{
			$injections[$inject->getTradingId()][] = $inject;
		}

		if (empty($injections)) { return; }

		// change injection settings

		foreach($setupIds as $id)
		{
			if (!isset($injections[$id])) { continue; }

			$setup = Trading\Setup\Model::wakeUp(['ID' => $id]);
			$setup->fill();
			$options = $setup->wakeupOptions();
			$optionsValues = $options->getValues();

			$fields = AutoInstallInjection::getSettingsFields($setup);
			$defaultValues = AutoInstallInjection::collectDefaultSettings($fields, ['INJECTION']);
			$oldSelectorsBasket = '.basket-checkout-block .fastorder, .basket-checkout-block.basket-checkout-block-btn'; //old selectors basket

			// aspro solution

			if ($optionsValues['SOLUTION'] === 'Aspro')
			{
				/** @var $injectObject Injection\Setup\Model*/
				foreach ($injections[$id] as $injectObject)
				{
					foreach ($defaultValues['INJECTION'] as $valueDefault)
					{
						if ($valueDefault['BEHAVIOR'] !== $injectObject->getBehavior()) { continue; }

						if ($valueDefault['BEHAVIOR'] === 'element')
						{
							$key = sprintf('%s_IBLOCK', mb_strtoupper($valueDefault['BEHAVIOR'])); //save iblock id
							$valueDefault['SETTINGS'][$key] = $injectObject->getSettings()[$key];
						}

						$injectObject->setSettings($valueDefault['SETTINGS']);
					}

					$injectObject->save();
				}
			}

			// default solution

			else
			{
				/** @var $injectObject Injection\Setup\Model*/
				foreach ($injections[$id] as $injectObject)
				{
					foreach ($defaultValues['INJECTION'] as $valueDefault)
					{
						if ($valueDefault['BEHAVIOR'] !== $injectObject->getBehavior()) { continue; }

						$settings = $injectObject->getSettings();

						$settings[mb_strtoupper($valueDefault['BEHAVIOR']) . '_DISPLAY'] = Injection\Behavior\Display\Registry::BUTTON;

						if ($valueDefault['BEHAVIOR'] === 'basket')
						{
							if ($settings['BASKET_SELECTOR'] !== $oldSelectorsBasket) { continue; }

							$injectObject->setSettings($valueDefault['SETTINGS']);
							continue;
						}

						$injectObject->setSettings($settings);
					}

					$injectObject->save();
				}
			}
		}
	});
}