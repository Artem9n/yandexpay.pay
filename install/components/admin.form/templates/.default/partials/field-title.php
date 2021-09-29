<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

/** @var $component YandexPay\Pay\Components\AdminForm */
/** @var $field array */
/** @var $arResult array */

$fieldTitle = $component->getFieldTitle($field);
$isRequired = ($field['MANDATORY'] === 'Y');

if (!empty($field['HELP']))
{
	?><span class="b-icon icon--question indent--right b-tag-tooltip--holder">
		<span class="b-tag-tooltip--content"><?= $field['HELP']; ?></span>
	</span><?php
}

if ($isRequired && !isset($arResult['DISABLE_REQUIRED_HIGHLIGHT']))
{
	?><strong><?= $fieldTitle; ?></strong><?php
}
else
{
	echo $fieldTitle;
}