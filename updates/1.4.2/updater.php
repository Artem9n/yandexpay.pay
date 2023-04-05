<?php

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles("install/js", "js/yandexpaypay");
	$updater->CopyFiles("install/components", "components/yandexpay.pay");

	AddEventHandler('main', 'OnModuleUpdate', static function($readyModules)
	{
		if (!Main\Loader::includeModule('main')) { return; }
		if (!Main\Loader::includeModule('sale')) { return; }
		if (!Main\Loader::includeModule('yandexpay.pay')) { return; }

		try
		{
			//update display fields for payment settings
			$result = [];

			$queryBusiness = Sale\Internals\BusinessValueTable::getList([
				'filter' => [
					'CODE_KEY' => ['YANDEX_PAY_VARIANT_BUTTON', 'YANDEX_PAY_WIDTH_BUTTON'],
					'PROVIDER_KEY' => 'INPUT',
				],
			]);

			while ($business = $queryBusiness->fetch())
			{
				$personTypeId = $business['PERSON_TYPE_ID'] ?? 0;
				$business['CONSUMER_KEY'] = $business['CONSUMER_KEY'] ?? 'COMMON';

				$result[$personTypeId][$business['CODE_KEY']] = $business;
			}

			if (empty($result)) { return; }

			foreach ($result as $personTypeId => $fields)
			{
				$fieldsDisplay = [
					'DISPLAY' => Pay\Injection\Behavior\Display\Registry::BUTTON,
					'VARIANT_BUTTON' => $fields['YANDEX_PAY_VARIANT_BUTTON']['PROVIDER_VALUE'],
					'WIDTH_BUTTON' => $fields['YANDEX_PAY_WIDTH_BUTTON']['PROVIDER_VALUE'],
					'WIDTH_VALUE_BUTTON' => '282',
					'HEIGHT_VALUE_BUTTON' => '54',
					'BORDER_RADIUS_VALUE_BUTTON' => '8',
				];

				$mapping = [
					'PROVIDER_VALUE' => serialize($fieldsDisplay),
					'PROVIDER_KEY' => 'INPUT',
				];

				$common = !IsModuleInstalled('bitrix24');
				Sale\BusinessValue::setMapping(
					'YANDEX_PAY_DISPLAY',
					$fields['YANDEX_PAY_VARIANT_BUTTON']['CONSUMER_KEY'],
					$personTypeId,
					$mapping,
					$common
				);
			}

			//update delivery type courier

			$queryOptions = Pay\Trading\Settings\RepositoryTable::getList([
				'filter' => [
					'=NAME' => 'DELIVERY_OPTIONS',
				],
			]);

			while ($option = $queryOptions->fetch())
			{
				$isChange = false;

				foreach ($option['VALUE'] as &$value)
				{
					if ($value['TYPE'] !== 'delivery') { continue; }

					$isChange = true;

					$value['TYPE'] = 'courier';
				}
				unset($value);

				if (!$isChange) { continue; }

				Pay\Trading\Settings\RepositoryTable::update(['ID' => $option['ID'], 'NAME' => $option['NAME']], [
					'VALUE' => $option['VALUE'],
				]);
			}
		}
		catch (\Throwable $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}
	});
}