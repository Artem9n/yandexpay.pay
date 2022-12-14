<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use Bitrix\Main;

/**
 * @var CMain $APPLICATION
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var array $arParams
 */

global $USER;

$userGroupId = $arResult['PARAMS']['userGroup'];
$anonymousGroups = Main\UserTable::getUserGroupIds(0);

if ($userGroupId === 0 || in_array($userGroupId, $anonymousGroups))
{
	$arResult['NEED_SHOW'] = true;
}
else
{
	$userGroups = Main\UserTable::getUserGroupIds((int)$USER->GetID());
	$arResult['NEED_SHOW'] = in_array($userGroupId, $userGroups);
}

include __DIR__ . '/modifier/assets.php';