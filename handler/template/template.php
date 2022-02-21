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
	'cardNetworks' => true,
	'gateway' => true,
	'gatewayMerchantId' => true,
	'externalId' => true,
    'paySystemId' => true,
    'currency' => true,
	'notifyUrl' => true,
]);
?>

<div>
	<?= Loc::getMessage('YANDEX_MARKET_SALE_SUM_DESCRIPTION', ['#SUM#' => CurrencyFormat($params['order']['total'], $params['currency'])])?>
</div>
<div id="yandexpay" class="bx-yapay-drawer"></div>

<script>
	(function() {
		const factory = new BX.YandexPay.Factory();
		const element = document.getElementById('yandexpay');
		const options = <?= Json::encode($widgetOptions) ?>;
		const widget = factory.install(element);

		widget.setOptions(options);
		widget.payment(<?= Json::encode($params['order']) ?>);
	})();
</script>
