<?php

use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Gateway;
use YandexPay\Pay\Utils;

/** @var $this \Sale\Handlers\PaySystem\YandexPayHandler */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('yandexpay.pay');

$data = [
	'NAME'        => Loc::getMessage('YANDEX_PAY_TITLE'),
	'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_DESCRIPTION'),
	'CODES'       => [
		'YANDEX_PAY_TEST_MODE' => [
			'NAME'          => Loc::getMessage('YANDEX_PAY_TEST_MODE_NAME'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_TEST_MODE_DESCRIPTION'),
			'SORT'          => 100,
			'INPUT'         => [
				'TYPE' => 'Y/N'
			],
			'DEFAULT'       => [
				'PROVIDER_KEY'      => 'INPUT',
				'PROVIDER_VALUE'    => 'Y'
			]
		],
		'YANDEX_PAY_MERCHANT_ID' => [
			'NAME'          => Loc::getMessage('YANDEX_PAY_MERCHANT_ID_NAME'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_MERCHANT_ID_DESCRIPTION'),
			'SORT'          => 150
		],
		'YANDEX_PAY_MERCHANT_NAME' => [
			'NAME'          => Loc::getMessage('YANDEX_PAY_MERCHANT_NAME_NAME'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_MERCHANT_NAME_DESCRIPTION'),
			'SORT'          => 200
		],
		'YANDEX_PAY_VARIANT_BUTTON' => [
			'NAME'          => Loc::getMessage('YANDEX_PAY_VARIANT_BUTTON_NAME'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_VARIANT_BUTTON_DESCRIPTION'),
			'SORT'          => 250,
			'TYPE'          => 'SELECT',
			'INPUT'         => [
				'TYPE'    => 'ENUM',
				'OPTIONS' => [
					'WHITE'             => Loc::getMessage('YANDEX_PAY_VARIANT_BUTTON_WHITE'),
					'WHITE-OUTLINED'    => Loc::getMessage('YANDEX_PAY_VARIANT_BUTTON_COUNTER'),
					'BLACK'             => Loc::getMessage('YANDEX_PAY_VARIANT_BUTTON_BLACK')
				]
			],
			'DEFAULT' => [
				'PROVIDER_VALUE'    => 'BLACK',
				'PROVIDER_KEY'      => 'INPUT'
			]
		],
		'YANDEX_PAY_WIDTH_BUTTON' => [
			'NAME'          => Loc::getMessage('YANDEX_PAY_WIDTH_BUTTON_NAME'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_WIDTH_BUTTON_DESCRIPTION'),
			'SORT'          => 300,
			'TYPE'          => 'SELECT',
			'INPUT'         => [
				'TYPE'    => 'ENUM',
				'OPTIONS' => [
					'AUTO' => Loc::getMessage('YANDEX_PAY_WIDTH_BUTTON_AUTO'),
					'MAX'  => Loc::getMessage('YANDEX_PAY_WIDTH_BUTTON_FULL'),
				]
			],
			'DEFAULT' => [
				'PROVIDER_VALUE'    => 'AUTO',
				'PROVIDER_KEY'      => 'INPUT'
			]
		],
		'YANDEX_PAY_NOTIFY_URL' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_NOTIFY_URL'),
			'SORT' => 350,
			'DEFAULT' => [
				'PROVIDER_KEY'      => 'VALUE',
				'PROVIDER_VALUE'    => Utils\Url::absolutizePath(BX_ROOT . '/tools/yandexpay.pay/sale_ps_yandexpay_result.php')
			]
		],
	],
];

try
{
	$gateway = null;

	if (isset($paySystem['PS_MODE']))
	{
		$gateway = Gateway\Manager::getProvider($paySystem['PS_MODE']);
	}
	else if ($request->get('PS_MODE') !== null) // pay_system_edit.php variable
	{
		$gateway = Gateway\Manager::getProvider($request->get('PS_MODE'));
	}
	else if (isset($this) && $this instanceof \Sale\Handlers\PaySystem\YandexPayHandler)
	{
		$handlerMode = $this->getHandlerMode();

		if ($handlerMode === null)
		{
			$query = \Bitrix\Sale\Internals\PaySystemActionTable::getList([
				'filter' => [
					'=ID' => $request->get('ID'),
					'=ACTION_FILE' => $this->service->getField('ACTION_FILE')
				],
				'select' => ['ID', 'PS_MODE', 'ACTION_FILE'],
				'limit' => 1
			]);

			if (($system = $query->fetch()) && $system['PS_MODE'] !== '')
			{
				$gateway = Gateway\Manager::getProvider($system['PS_MODE']);
			}

			if ($gateway === null)
			{
				$list = Gateway\Manager::getHandlerModeList();
				reset($list);
				$gateway = Gateway\Manager::getProvider(key($list));
			}
		}
		else
		{
			$gateway = Gateway\Manager::getProvider($handlerMode);
		}
	}

	if ($gateway !== null)
	{
		$data['CODES'] += $gateway->getParams();
		$psDescription = $gateway->getDescription();
	}
}
catch (\Bitrix\Main\SystemException $exception)
{
	// nothing
}
