<?php

$jsConfig = [
	'yandex_pay_sdk' => [
		'js' => 'https://pay.yandex.ru/sdk/v1/pay.js'
	],
	'yandex_pay_load' => [
		'js' => BX_ROOT . '/js/yandexpay.pay/yandexpayload.js',
		'css' => BX_ROOT . '/css/yandexpay.pay/yandexpay.css',
	]
];

foreach ($jsConfig as $ext => $arExt)
{
	\CJSCore::RegisterExt($ext, $arExt);
}