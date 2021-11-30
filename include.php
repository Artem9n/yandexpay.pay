<?php

$jsConfig = [
	'yandexpay.sdk' => [
		'js' => 'https://sandbox.pay.yandex.ru/sdk/v1/pay.js',
		//'js' => 'https://pay.yandex.ru/sdk/v1/pay.js'
	],
];

foreach ($jsConfig as $ext => $arExt)
{
	\CJSCore::RegisterExt($ext, $arExt);
}