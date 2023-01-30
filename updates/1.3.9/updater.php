<?php

use Bitrix\Main;
use YandexPay\Pay\Trading;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles("install/components", "components/yandexpay.pay");
}