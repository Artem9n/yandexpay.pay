<?php

/** @global CMain $APPLICATION */
use Bitrix\Main\Localization\Loc;

$accessLevel = (string)CMain::GetUserRight('yandexpay.pay');

if ($accessLevel <= 'D') { return false; }

Loc::loadMessages(__FILE__);

return [];
