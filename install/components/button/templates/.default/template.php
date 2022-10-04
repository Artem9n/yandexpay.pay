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

try
{
	// assets

	if ($APPLICATION->GetPageProperty('yandexpay_extension_widget') !== 'Y')
	{
		$APPLICATION->SetPageProperty('yandexpay_extension_widget', 'Y');
		echo str_replace('<script', '<script defer ', Extension::getHtml('yandexpaypay.widget'));
	}

	if (
		!empty($arResult['PARAMS']['solution'])
		&& $APPLICATION->GetPageProperty('yandexpay_extension_' . $arResult['PARAMS']['solution']) !== 'Y'
	)
	{
		$APPLICATION->SetPageProperty('yandexpay_extension_' . $arResult['PARAMS']['solution'], 'Y');

		$solution = Solution\Registry::getInstance($arResult['PARAMS']['solution']);
		echo str_replace('<script', '<script defer ', $solution->getExtension());
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
		'buttonWidth' => true,
	]);
	$factoryOptions += (array)($arParams['~FACTORY_OPTIONS'] ?? []);
	$factoryOptions['label'] = GetMessage('YANDEXPAY_BUTTON_LABEL');
	$factoryOptions['containerId'] = $containerId;
	$order = $arResult['PARAMS']['order'];
	$selector = $arResult['PARAMS']['selector'];
	$position = $arResult['PARAMS']['position'];

	if (!empty($selector))
	{
		?>
		<script>
			(function() {
				wait();

				function wait() {
					if (
						typeof BX !== 'undefined'
						&& typeof BX.YandexPay !== 'undefined'
						&& typeof BX.YandexPay.Factory !== 'undefined'
					) {
						ready();
						return;
					}

					setTimeout(wait, 500);
				}

				function ready() {
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
				};
			})();
		</script>
		<?php
	}
	else
	{
		?>
		<div id="<?= $containerId ?>" class="yandex-pay"></div>
		<script>
			(function() {
				wait();

				function wait() {
					if (
						typeof BX !== 'undefined'
						&& typeof BX.YandexPay !== 'undefined'
						&& typeof BX.YandexPay.Factory !== 'undefined'
					) {
						ready();
						return;
					}

					setTimeout(wait, 500);
				}

				function ready() {
					const factory = new BX.YandexPay.Factory(<?= Json::encode($factoryOptions) ?>);
					const element = document.getElementById('<?= $containerId?>');

					factory.create(element)
						.then((widget) => {
							widget.setOptions(<?= Json::encode($widgetOptions) ?>);
							widget.cart();
						})
						.catch((error) => {
							console.warn(error);
						});
				};
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
