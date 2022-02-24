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

try
{
	echo Extension::getHtml('yandexpaypay.widget');

	if (!empty($arResult['PARAMS']['solution']))
	{
		$solution = Solution\Registry::getInstance($arResult['PARAMS']['solution']);
		echo $solution->getExtension();
	}

	$widgetOptions = array_diff_key($arResult['PARAMS'], [ 'order' => true , 'selector' => true, 'position' => true]);
	$factoryOptions = array_intersect_key($arResult['PARAMS'], [
		'solution' => true,
		'mode' => true,
		'buttonWidth' => true,
	]);
	$factoryOptions['label'] = GetMessage('YANDEXPAY_BUTTON_LABEL');
	$order = $arResult['PARAMS']['order'];
	$selector = $arResult['PARAMS']['selector'];
	$position = $arResult['PARAMS']['position'];

	if (!empty($selector))
	{
		?>
		<script>
			(function() {
				const factory = new BX.YandexPay.Factory(<?= Json::encode($factoryOptions) ?>);
				const selector = '<?= htmlspecialcharsback($selector) ?>';
				const position = '<?= $position?>';

				factory.inject(selector, position)
					.then((widget) => {
						widget.setOptions(<?= Json::encode($widgetOptions) ?>);
						widget.cart();
					})
					.catch((error) => {
						console.warn(error);
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
				const factory = new BX.YandexPay.Factory(<?= Json::encode($factoryOptions) ?>);
				const element = document.getElementById('yandexpay');
				const widget = factory.install(element);

				widget.setOptions(<?= Json::encode($widgetOptions) ?>);
				widget.cart();
			})();
		</script>
		<?php
	}
}
catch (Main\SystemException $exception)
{
	if (!isset($USER) || !$USER->IsAdmin()) { return; }

	ShowError($exception->getMessage());
}
