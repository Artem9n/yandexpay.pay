<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/** @var $component \YandexPay\Pay\Components\AdminGrid */
/** @var $this \CBitrixComponentTemplate */

$this->IncludeLangFile('template.php');

if (!isset($templateFolder)) { $templateFolder = $this->__folder; }
if (!isset($component)) { $component = $this->__component; }

$arResult['LIST_EXTENSION'] = [];

include __DIR__ . '/modifier/read-only.php';
include __DIR__ . '/modifier/load-more.php';
include __DIR__ . '/modifier/reload-events.php';