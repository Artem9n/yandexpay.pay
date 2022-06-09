<?php

define('NO_AGENT_CHECK', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('DisableEventsCheck', true);

require_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_before.php';

$APPLICATION->IncludeComponent('yandexpay.pay:purchase.rest', '', [
	'SEF_FOLDER' => BX_ROOT . '/services/yandexpay.pay/trading',
], false, [ 'HIDE_ICONS' => 'Y' ]);

require_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_after.php';