<?php

/** @global CMain $APPLICATION */
use Bitrix\Main\Localization\Loc;

$accessLevel = (string)CMain::GetUserRight('yandexpay.pay');

if ($accessLevel <= 'D') { return false; }

Loc::loadMessages(__FILE__);

return [
	[
		'parent_menu' => 'global_menu_services',
		'section' => 'yapay_pay',
		'sort' => 1000,
		'text' => Loc::getMessage('YANDEX_PAY_MENU_ROOT'),
		'title' => Loc::getMessage('YANDEX_PAY_MENU_ROOT'),
		'icon' => 'yapay_pay_icon',
		'items_id' => 'menu_yapay_pay',
		'items' => [
			[
				'text' => Loc::getMessage('YANDEX_PAY_MENU_TRADING_SETUP'),
				'title' => Loc::getMessage('YANDEX_PAY_MENU_TRADING_SETUP'),
				'url' => 'yapay_trading_grid.php?lang=' . LANGUAGE_ID,
				'more_url' => [
					'yapay_trading_edit.php?lang=' . LANGUAGE_ID,
					'yapay_trading_setup.php?lang=' . LANGUAGE_ID,
				],
			],
            [
                'text' => Loc::getMessage('YANDEX_PAY_MENU_TRADING_INJECTION'),
                'title' => Loc::getMessage('YANDEX_PAY_MENU_TRADING_INJECTION'),
                'url' => 'yapay_trading_injection_grid.php?lang=' . LANGUAGE_ID,
                'more_url' => [
                    'yapay_trading_injection_edit.php?lang=' . LANGUAGE_ID,
                    'yapay_trading_injection_setup.php?lang=' . LANGUAGE_ID,
                ],
            ],
		],
	],
];
