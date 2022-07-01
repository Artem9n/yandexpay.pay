<?php

use YandexPay\Pay;
use Bitrix\Main;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles("install/js", "js/yandexpaypay");
	$updater->CopyFiles("install/components", "components/yandexpay.pay");
}
?>