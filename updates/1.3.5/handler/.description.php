<?php

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Gateway;
use YandexPay\Pay\Utils;
use YandexPay\Pay\Ui as PayUi;

/** @var $this \Sale\Handlers\PaySystem\YandexPayHandler */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }

Loc::loadMessages(__FILE__);

if (!Main\Loader::includeModule('yandexpay.pay'))
{
	trigger_error(E_USER_WARNING, 'Module yandexpay.pay is required');
	$data = [];
	return;
}

PayUi\SaleInput\Registry::register();

// common

$data = [
	'NAME'        => Loc::getMessage('YANDEX_PAY_TITLE'),
	'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_DESCRIPTION'),
	'CODES'       => [
		'YANDEX_PAY_SETTING' => [
			'NAME'          => ' ',
			'SORT'          => 120,
			'INPUT' => [
				'TYPE' => PayUi\SaleInput\Registry::type(PayUi\SaleInput\Registry::TYPE_MERCHANT),
				'SIZE' => 40
			],
		],
		'YANDEX_PAY_MERCHANT_ID' => [
			'NAME'          => Loc::getMessage('YANDEX_PAY_MERCHANT_ID_NAME'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_MERCHANT_ID_DESCRIPTION'),
			'SORT'          => 150,
			'INPUT' => [
				'TYPE' => 'STRING',
				'SIZE' => 40
			],
		],
		'YANDEX_PAY_REST_API_KEY' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_REST_API_KEY_NAME'),
			'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_REST_API_KEY_DESCRIPTION'),
			'SORT' => 155,
			'INPUT' => [
				'TYPE' => 'STRING',
				'SIZE' => 40,
			],
		],
		'YANDEX_PAY_MERCHANT_NAME' => [
			'NAME'          => Loc::getMessage('YANDEX_PAY_MERCHANT_NAME_NAME'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_MERCHANT_NAME_DESCRIPTION'),
			'SORT'          => 200,
			'INPUT' => [
				'TYPE' => 'STRING',
				'SIZE' => 40,
			]
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
		'YANDEX_PAY_SUCCESS_URL' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_SUCCESS_URL'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_SUCCESS_URL_DESCRIPTION'),
			'SORT' => 310,
			'INPUT' => [
				'TYPE' => 'STRING',
				'SIZE' => 40,
			],
		],
		'YANDEX_PAY_FAIL_URL' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_FAIL_URL'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_FAIL_URL_DESCRIPTION'),
			'SORT' => 320,
			'INPUT' => [
				'TYPE' => 'STRING',
				'SIZE' => 40,
			],
		],
		'YANDEX_PAY_NOTIFY_URL' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_NOTIFY_URL'),
			'SORT' => 350,
			'INPUT' => [
				'TYPE' => 'STRING',
				'SIZE' => 40,
				'VALUE' => Utils\Url::absolutizePath(BX_ROOT . '/tools/yandexpay.pay/sale_ps_yandexpay_result.php')
			],
		],
		'YANDEX_PAY_TEST_MODE' => [
			'NAME'          => Loc::getMessage('YANDEX_PAY_TEST_MODE_NAME'),
			'DESCRIPTION'   => Loc::getMessage('YANDEX_PAY_TEST_MODE_DESCRIPTION'),
			'SORT'          => 353,
			'GROUP'         => Loc::getMessage('YANDEX_PAY_ADNVANCED_SETTINGS_GROUP'),
			'INPUT'         => [
				'TYPE' => 'Y/N'
			],
			'DEFAULT'       => [
				'PROVIDER_KEY'      => 'INPUT',
				'PROVIDER_VALUE'    => 'N'
			]
		],
		'YANDEX_PAY_LOG_LEVEL' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_LOG_LEVEL_NAME'),
			'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_LOG_LEVEL_DESCRIPTION'),
			'SORT' => 355,
			'GROUP' => Loc::getMessage('YANDEX_PAY_ADNVANCED_SETTINGS_GROUP'),
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => \YandexPay\Pay\Logger\Level::getEnum(),
			],
			'DEFAULT' => [
				'PROVIDER_VALUE'    => \YandexPay\Pay\Logger\Level::INFO,
				'PROVIDER_KEY'      => 'INPUT',
			]
		],
		'YANDEX_PAY_STATUS_ORDER_AUTO_PAY' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_AUTO_PAY_NAME'),
			'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_AUTO_PAY_DESCRIPTION'),
			'GROUP' => Loc::getMessage('YANDEX_PAY_CHECKOUT_GROUP'),
			'SORT' => 360,
			'INPUT'         => [
				'TYPE' => 'Y/N'
			],
			'DEFAULT'       => [
				'PROVIDER_KEY'      => 'INPUT',
				'PROVIDER_VALUE'    => 'Y'
			]
		],
		/*'YANDEX_PAY_STATUS_ORDER_STAGE_PAY' => [
			'NAME' => '����������� �������',
			'DESCRIPTION' => '������������� ����� �������� ������� � ���������������� (������������)',
			'GROUP' => '������������ Yandex Pay Checkout',
			'SORT' => 370,
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => [
					'SINGLE' => '������������� ������',
					'TWO' => '������������� ������'
				]
			],
			'DEFAULT' => [
				'PROVIDER_VALUE'    => 'TWO',
				'PROVIDER_KEY'      => 'INPUT'
			]
		],*/
		'YANDEX_PAY_STATUS_ORDER_HOLD' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_HOLD_NAME'),
			'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_HOLD_DESCRIPTION'),
			'GROUP' => Loc::getMessage('YANDEX_PAY_CHECKOUT_GROUP'),
			'SORT' => 450,
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => \YandexPay\Pay\Trading\Entity\Sale\Status::getEnum()
			],
			'DEFAULT' => [
				'PROVIDER_VALUE'    => \YandexPay\Pay\Trading\Entity\Sale\Status::orderAuthorize(),
				'PROVIDER_KEY'      => 'INPUT'
			]
		],
		'YANDEX_PAY_STATUS_ORDER_CAPTURE' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_CAPTURE_NAME'),
			'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_CAPTURE_DESCRIPTION'),
			'GROUP' => Loc::getMessage('YANDEX_PAY_CHECKOUT_GROUP'),
			'SORT' => 500,
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => \YandexPay\Pay\Trading\Entity\Sale\Status::getEnum()
			],
			'DEFAULT' => [
				'PROVIDER_VALUE'    => \YandexPay\Pay\Trading\Entity\Sale\Status::orderCapture(),
				'PROVIDER_KEY'      => 'INPUT'
			]
		],
		'YANDEX_PAY_STATUS_ORDER_CANCEL' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_CANCEL_NAME'),
			'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_CANCEL_DESCRIPTION'),
			'GROUP' => Loc::getMessage('YANDEX_PAY_CHECKOUT_GROUP'),
			'SORT' => 550,
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => \YandexPay\Pay\Trading\Entity\Sale\Status::getEnum()
			],
			'DEFAULT' => [
				'PROVIDER_VALUE'    => \YandexPay\Pay\Trading\Entity\Sale\Status::orderCancel(),
				'PROVIDER_KEY'      => 'INPUT'
			]
		],
		'YANDEX_PAY_STATUS_ORDER_REFUND' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_REFUND_NAME'),
			'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_REFUND_DESCRIPTION'),
			'GROUP' => Loc::getMessage('YANDEX_PAY_CHECKOUT_GROUP'),
			'SORT' => 600,
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => \YandexPay\Pay\Trading\Entity\Sale\Status::getEnum()
			],
			'DEFAULT' => [
				'PROVIDER_VALUE'    => \YandexPay\Pay\Trading\Entity\Sale\Status::orderRefund(),
				'PROVIDER_KEY'      => 'INPUT'
			]
		],
		'YANDEX_PAY_STATUS_ORDER_PARTIALLY_REFUND' => [
			'NAME' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_PARTIALLY_REFUND_NAME'),
			'DESCRIPTION' => Loc::getMessage('YANDEX_PAY_STATUS_ORDER_PARTIALLY_REFUND_DESCRIPTION'),
			'GROUP' => Loc::getMessage('YANDEX_PAY_CHECKOUT_GROUP'),
			'SORT' => 650,
			'INPUT' => [
				'TYPE' => 'ENUM',
				'OPTIONS' => \YandexPay\Pay\Trading\Entity\Sale\Status::getEnum()
			],
			'DEFAULT' => [
				'PROVIDER_VALUE'    => \YandexPay\Pay\Trading\Entity\Sale\Status::orderPartiallyRefund(),
				'PROVIDER_KEY'      => 'INPUT'
			]
		],
	],
];

// gateway

try
{
	$gateway = null;
	$request = Main\Application::getInstance()->getContext()->getRequest();
	$apiKey = null;

	if ($request->get('PS_MODE') !== null)
	{
		$gateway = Gateway\Manager::getProvider($request->get('PS_MODE'));
	}
	else if (isset($paySystem['PS_MODE'])) // pay_system_edit.php variable
	{
		$gateway = Gateway\Manager::getProvider($paySystem['PS_MODE']);
	}
	else if (isset($this) && $this instanceof \Sale\Handlers\PaySystem\YandexPayHandler)
	{
		$handlerMode = $this->getHandlerMode();
		$apiKey = Sale\BusinessValue::get(
			'YANDEX_PAY_REST_API_KEY',
			Sale\PaySystem\Service::PAY_SYSTEM_PREFIX . $request->get('ID')
		);

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

			if (($system = $query->fetch()) && (string)$system['PS_MODE'] !== '')
			{
				$gateway = Gateway\Manager::getProvider($system['PS_MODE']);
			}
		}
		else
		{
			$gateway = Gateway\Manager::getProvider($handlerMode);
		}
	}

	if ($gateway !== null)
	{
		if (Gateway\Manager::resolveGatewayRest($gateway->getId()))
		{
			if ($apiKey === null)
			{
				$data['CODES'] += $gateway->getParams();
			}
			else
			{
				unset(
					$data['CODES']['YANDEX_PAY_MERCHANT_NAME'],
					$data['CODES']['YANDEX_PAY_NOTIFY_URL']
				);
			}
		}
		else
		{
			$data['CODES'] += $gateway->getParams();
			$psDescription = $gateway->getDescription();
		}
	}
	else
	{
		unset(
			$data['CODES']['YANDEX_PAY_MERCHANT_NAME'],
			$data['CODES']['YANDEX_PAY_NOTIFY_URL']
		);
	}
}
catch (\Bitrix\Main\SystemException $exception)
{
	// nothing
}
