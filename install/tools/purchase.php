<?php

define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define('DisableEventsCheck', true);

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $APPLICATION;

$APPLICATION->IncludeComponent('yandexpay.pay:purchase', '');

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';