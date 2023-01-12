<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/**
 * @var CMain $APPLICATION
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Main;
use YandexPay\Pay\Injection\Solution;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

$this->setFrameMode(true); // not need dynamic area
if (!$arResult['NEED_SHOW']) { return; }
try
{
	// assets

	if ($APPLICATION->GetPageProperty('yandexpay_extension_widget') !== 'Y')
	{
		$APPLICATION->SetPageProperty('yandexpay_extension_widget', 'Y');
		echo Extension::getHtml('yandexpaypay.widget');
	}

	if (
		!empty($arResult['PARAMS']['solution'])
		&& $APPLICATION->GetPageProperty('yandexpay_extension_' . $arResult['PARAMS']['solution']) !== 'Y'
	)
	{
		$APPLICATION->SetPageProperty('yandexpay_extension_' . $arResult['PARAMS']['solution'], 'Y');

		$solution = Solution\Registry::getInstance($arResult['PARAMS']['solution']);
		echo $solution->getExtension();
	}

	// widget index

	$widgetIndex = (int)$APPLICATION->GetPageProperty('yandexpay_widget_index');
	$containerId = 'yandexpay' . ($widgetIndex > 0 ? '-' . $widgetIndex : '');

	$APPLICATION->SetPageProperty('yandexpay_widget_index', ++$widgetIndex);

	// draw

	$widgetOptions = array_diff_key($arResult['PARAMS'], [ 'order' => true , 'selector' => true, 'position' => true]);
	$widgetOptions += (array)($arParams['~WIDGET_OPTIONS'] ?? []);
	$factoryOptions = array_intersect_key($arResult['PARAMS'], [
		'solution' => true,
		'mode' => true,
		'displayType' => true,
		'displayParameters' => true,
		'useDivider' => true,
	]);
	$factoryOptions += (array)($arParams['~FACTORY_OPTIONS'] ?? []);
	$factoryOptions['label'] = GetMessage('YANDEXPAY_BUTTON_LABEL');
	$factoryOptions['containerId'] = $containerId;
	$order = $arResult['PARAMS']['order'];
	$selector = $arResult['PARAMS']['selector'];
	$position = $arResult['PARAMS']['position'];

	if (empty($selector))
	{
		$selector = '#' . $containerId . '-container';
		$position = 'afterbegin';
		$factoryOptions += [
			'preserve' => false,
		];

		?>
		<div id="<?= $containerId . '-container' ?>" class="yandex-pay"></div>
		<?php
	}
	?>
	<script>
		(function() {
			<?php
			echo file_get_contents(__DIR__ . '/init.min.js');
			?>
			function run() {
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
			}
		})();
	</script>
	<?php
}
catch (Main\SystemException $exception)
{
	if (!isset($USER) || !$USER->IsAdmin()) { return; }

	ShowError($exception->getMessage());
}
