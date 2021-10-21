<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/**
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

echo Extension::getHtml('yandexpaypay.widget');

?>
<div id="yandexpay" class="yandex-pay"></div>
<script>
	(function() {
		const element = document.getElementById('yandexpay');
		const widget = new BX.YandexPay.Widget(element, <?= Json::encode($arResult['PARAMS']) ?>);

		widget.cart(<?= Json::encode($arResult['PARAMS']['order']) ?>);
	})();
</script>
