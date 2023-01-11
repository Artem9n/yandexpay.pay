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

// widget index

$arResult['WIDGET_INDEX'] = (int)$APPLICATION->GetPageProperty('yandexpay_widget_index');
$arResult['CONTAINER_ID'] = 'yandexpay' . ($arResult['WIDGET_INDEX'] > 0 ? '-' . $arResult['WIDGET_INDEX'] : '');

$APPLICATION->SetPageProperty('yandexpay_widget_index', ++$arResult['WIDGET_INDEX']);

// params for draw

$arResult['WIDGET_OPTIONS'] = array_diff_key($arResult['PARAMS'], [ 'order' => true , 'selector' => true, 'position' => true]);
$arResult['WIDGET_OPTIONS'] += (array)($arParams['~WIDGET_OPTIONS'] ?? []);
$arResult['FACTORY_OPTIONS'] = array_intersect_key($arResult['PARAMS'], [
	'solution' => true,
	'mode' => true,
	'displayType' => true,
	'displayParameters' => true,
	'useDivider' => true,
]);
$arResult['FACTORY_OPTIONS'] += (array)($arParams['~FACTORY_OPTIONS'] ?? []);
$arResult['FACTORY_OPTIONS']['label'] = GetMessage('YANDEXPAY_BUTTON_LABEL');
$arResult['FACTORY_OPTIONS']['containerId'] = $arResult['CONTAINER_ID'];
$arResult['ORDER'] = $arResult['PARAMS']['order'];
$arResult['SELECTOR'] = htmlspecialcharsback($arResult['PARAMS']['selector']);
$arResult['POSITION'] = $arResult['PARAMS']['position'];

// css/js content

if ($arResult['PARAMS']['jsContent'] !== null)
{
	$arResult['JS_CONTENT'] = str_replace('{$container}', '#' . $arResult['CONTAINER_ID'], $arResult['PARAMS']['jsContent']);
}

if ($arResult['PARAMS']['cssContent'] !== null)
{
	$arResult['CSS_CONTENT'] = str_replace('{$container}', '#' . $arResult['CONTAINER_ID'], $arResult['PARAMS']['cssContent']);
}

include __DIR__ . '/modifier/assets.php';