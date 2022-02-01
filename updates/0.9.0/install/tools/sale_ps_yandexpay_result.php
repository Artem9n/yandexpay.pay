<?php

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Sale\PaySystem;

define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define("DisableEventsCheck", true);
define('PUBLIC_AJAX_MODE', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $APPLICATION;

if (
	Main\Loader::IncludeModule("sale")
	&& Main\Loader::includeModule('yandexpay.pay')
)
{
	$context = Application::getInstance()->getContext();
	$request = $context->getRequest();
	$request->addFilter(new \YandexPay\Pay\Utils\JsonBodyFilter());
	$request->addFilter(new \YandexPay\Pay\Utils\Secure3dBodyFilter());

	$payment = $request->get('payment');

	$paySystem = $request->get('paySystemId') ?? $payment['metadata']['paySystemId'];

	$item = PaySystem\Manager::getList([
		'filter' => [
			'=ACTION_FILE' => 'yandexpay',
			'=ID' => $paySystem,
			'ACTIVE' => 'Y'
		],
		'select' => ['*']
	])->fetch();

	if ($item !== false)
	{
		$service = new PaySystem\Service($item);
		$result = $service->processRequest($request);
	}
	else
	{
		$debugInfo = http_build_query($request->toArray(), "", "\n");
		if (empty($debugInfo))
		{
			$debugInfo = file_get_contents('php://input');
		}
		PaySystem\Logger::addDebugInfo('Pay system not found. Request: '.($debugInfo ?: "empty"));
	}
}

CMain::FinalActions();