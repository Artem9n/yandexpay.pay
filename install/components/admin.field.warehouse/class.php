<?php
namespace YandexPay\Pay\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

/** @noinspection PhpUnused */
class AdminFieldWarehouse extends \CBitrixComponent
{
	public function executeComponent()
	{
		$this->includeComponentTemplate();
	}
}