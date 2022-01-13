<?php

use Bitrix\Main;
use YandexPay\Pay;

/** @var CMain $APPLICATION */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$request = Main\Context::getCurrent()->getRequest();
$assets = Main\Page\Asset::getInstance();
$requestView = $request->get('view');

if ($requestView === 'dialog')
{
	$assets = $assets->setAjax();
	$APPLICATION->oAsset = $assets;
}
else
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
}

try
{
	if (!Main\Loader::includeModule('yandexpay.pay'))
	{
		throw new Main\SystemException('Module yandexpay.pay is required');
	}

	$controller = new Pay\Ui\Trading\SetupMerchant();

	$controller->checkReadAccess();
	$controller->loadModules();

	if ($requestView === 'dialog')
	{
		$controller->setLayout('raw');
	}

	$controller->show();
}
catch (Main\SystemException $exception)
{
	\CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => $exception->getMessage(),
	]);
}

if ($requestView === 'dialog')
{
	echo $assets->getCss();
	echo $assets->getJs();
}
else
{
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_before.php';
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_after.php';
