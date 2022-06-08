<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

// js extension

$jsConfig = [
	'yandexpay.sdk' => [
		'js' => 'https://pay.yandex.ru/sdk/v1/pay.js'
	],
];

foreach ($jsConfig as $ext => $arExt)
{
	\CJSCore::RegisterExt($ext, $arExt);
}

// vendor

if (CheckVersion(ModuleManager::getVersion('main'), '21.0.0'))
{
	Loader::registerNamespace('Firebase\\JWT', __DIR__ . '/vendor/firebase/php-jwt/src');
}
else
{
	require_once __DIR__ . '/vendor/autoload.php';
}
