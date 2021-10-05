<?php

use Bitrix\Main\Localization\Loc;
use YandexPay\Pay;

/** @var $this \Bitrix\Sale\PaySystem\BaseServiceHandler */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$host = $request->isHttps() ? 'https' : 'http';

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
				'PROVIDER_VALUE'    => 'N'
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
				'PROVIDER_VALUE'    => $host.'://'.$request->getHttpHost().'/bitrix/tools/sale_ps_yandexpay_result.php'
			]
		],
		/*'YANDEX_CARD_NETWORK' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_NAME'),
			'SORT' => 350,
			'INPUT' => [
				'TYPE'      => 'ENUM',
				'MULTIPLE'  => 'Y',
				'OPTIONS' => [
					'VISA'          => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_TYPE_VISA'),
					'MASTERCARD'    => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_TYPE_MASTERCARD'),
					'MIR'           => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_TYPE_MIR'),
					'MAESTRO'       => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_TYPE_MAESTRO'),
					'VISAELECTRON'  => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_TYPE_VISAELECTRON'),
					'UNIONPAY'      => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_TYPE_UNIONPAY'),
					'UZCARD'        => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_TYPE_UUZCARD'),
					'DISCOVER'      => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_TYPE_DISCOVER'),
					'AMEX'          => Loc::getMessage('YANDEX_PAY_CARD_NETWORK_TYPE_AMEX')
				]
			],
			'DEFAULT' => [
				'PROVIDER_KEY'      => 'INPUT',
				'PROVIDER_VALUE'    => [
					'VISA',
					'MASTERCARD',
					'MIR',
					'MAESTRO',
					'VISAELECTRON'
				]
			]
		],*/
	] + Pay\Gateway\Manager::getParams()
];

$psDescription = Pay\Gateway\Manager::getModeDescription();