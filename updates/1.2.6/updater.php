<?php
use Bitrix\Main;

/** @var $updater \CUpdater */

if (Main\ModuleManager::isModuleInstalled('yandexpay.pay'))
{
	$updater->CopyFiles("install/js", "js/yandexpaypay");
}
?>