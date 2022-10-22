<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main;
use YandexPay\Pay;

$jquery = 'jquery3';

if (Main\Loader::includeModule('yandexpay.pay'))
{
	$jquery = Pay\Admin\Library::resolve('jquery3', [
		'jquery2',
		'jquery',
	]);
}

return [
	'rel' => [ $jquery ],
];
