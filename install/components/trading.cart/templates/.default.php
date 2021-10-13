<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/**
 * @var array $params
 * @var $this \Sale\Handlers\PaySystem\YandexPayHandler
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

echo Extension::getHtml('yandexpaypay.widget');

$widgetOptions = array_intersect_key($params, [
	'requestSign' => true,
	'env' => true,
	'merchantId' => true,
	'merchantName' => true,
	'buttonTheme' => true,
	'buttonWidth' => true,
	'gateway' => true,
	'gatewayMerchantId' => true,
	'externalId' => true,
	'paySystemId' => true,
	'currency' => true,
	'YANDEX_PAY_NOTIFY_URL' => true,
]);

?>
<div>
	<?= Loc::getMessage('YANDEX_MARKET_SALE_SUM_DESCRIPTION', ['#SUM#' => CurrencyFormat($params['order']['total'], $params['currency'])])?>
</div>
<div id="yandexpay" class="yandex-pay"></div>
<script>
	(function() {
		const element = document.getElementById('yandexpay');
		const widget = new BX.YandexPay.Widget(element, <?= Json::encode($widgetOptions) ?>);

		widget.cart(<?= Json::encode($params['order']) ?>);
	})();
</script>
