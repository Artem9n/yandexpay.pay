<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

/** @var $component \YandexPay\Pay\Components\AdminGrid */

$disabledRows = [];

foreach ($component->getViewList()->aRows as $row)
{
	if ($row->bReadOnly)
	{
		$disabledRows[] = $row->id;
	}
}

if (!empty($disabledRows))
{
	$arResult['LIST_EXTENSION']['disabledRows'] = $disabledRows;
}