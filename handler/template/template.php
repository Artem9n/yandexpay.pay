<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/** @var array $params
 * @var $this \Sale\Handlers\PaySystem\YandexPayHandler
 */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

echo CJSCore::Init(['yandex_pay_sdk', 'yandex_pay_load'], true);

?>
<div>
	<?= Loc::getMessage('YANDEX_MARKET_SALE_SUM_DESCRIPTION', ['#SUM#' => CurrencyFormat($params['order']['total'], $params['currency'])])?>
</div>
<div id="yandexpay" class="yandex-pay"></div>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		let element = document.getElementById('yandexpay');
		YandexPay.load(element, <?= \Bitrix\Main\Web\Json::encode($params)?>);
	});
</script>
