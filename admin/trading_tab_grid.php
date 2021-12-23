<?php

use Bitrix\Main;
use YandexPay\Pay;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

try
{
	if (!Main\Loader::includeModule('yandexpay.pay'))
	{
		throw new Main\SystemException('Module yandexpay.pay is required');
	}

	$controller = new Pay\Ui\Trading\SetupTabGrid();

	$controller->markTarget();

	$controller->checkReadAccess();
	$controller->loadModules();

	$controller->show();
}
catch (Main\SystemException $exception)
{
	\CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => $exception->getMessage(),
	]);
}


require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_after.php';