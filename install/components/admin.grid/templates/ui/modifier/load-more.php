<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/** @var $component \YandexPay\Pay\Components\AdminGrid */

$adminList = $component->getViewList();

if ($adminList instanceof CAdminUiList && $adminList->enableNextPage)
{
	$arResult['LIST_EXTENSION']['loadMore'] = true;
}