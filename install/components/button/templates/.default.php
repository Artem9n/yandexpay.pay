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

$widgetOptions = array_diff_key($arResult['PARAMS'], [ 'order' => true , 'selector' => true, 'position' => true]);
$order = $arResult['PARAMS']['order'];
$selector = $arResult['PARAMS']['selector'];
$position = $arResult['PARAMS']['position'];

if (!empty($selector))
{
	?>
	<script>
		(function() {
			const factory = new BX.YandexPay.Factory();
			const selector = '<?= $selector ?>';
			const position = '<?= $position?>';
			const options = <?= Json::encode($widgetOptions) ?>;

			factory.inject(selector, position)
				.then((widget) => {
					widget.setOptions(options);
					widget.cart(<?= Json::encode($order) ?>);
				});
		})();
	</script>
	<?php
}
else
{
	?>
	<div id="yandexpay" class="yandex-pay"></div>
	<script>
		(function() {
			const factory = new BX.YandexPay.Factory();
			const element = document.getElementById('yandexpay');
			const options = <?= Json::encode($widgetOptions) ?>;
			const widget = factory.install(element);

			widget.setOptions(options);
			widget.cart(<?= Json::encode($order) ?>);
		})();
	</script>
	<?php
}?>