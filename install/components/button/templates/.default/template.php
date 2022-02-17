<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/**
 * @var array $arResult
 */

use Bitrix\Main;
use YandexPay\Pay\Injection\Solution;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

echo Extension::getHtml('yandexpaypay.widget');

if (!empty($arResult['PARAMS']['solution']))
{
	try
	{
		$solution = Solution\Registry::getInstance($arResult['PARAMS']['solution']);
		echo $solution->getExtension();
	}
	catch (Main\SystemException $exception)
	{
		//nothing
	}
}

$widgetOptions = array_diff_key($arResult['PARAMS'], [ 'order' => true , 'selector' => true, 'position' => true]);
$order = $arResult['PARAMS']['order'];
$selector = $arResult['PARAMS']['selector'];
$position = $arResult['PARAMS']['position'];

if (!empty($selector))
{
	$factoryOptions = array_intersect_key($arResult['PARAMS'], [
		'solution' => true,
		'mode' => true,
		'width' => true,
	]);
	$factoryOptions['label'] = GetMessage('YANDEXPAY_BUTTON_LABEL');

	?>
	<script>
		(function() {
			const factory = new BX.YandexPay.Factory(<?= Json::encode($factoryOptions) ?>);
			const selector = '<?= htmlspecialcharsback($selector) ?>';
			const position = '<?= $position?>';

			factory.inject(selector, position)
				.then((widget) => {
					widget.setOptions(<?= Json::encode($widgetOptions) ?>);
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
}
