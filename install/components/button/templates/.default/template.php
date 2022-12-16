<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/**
 * @var CMain $APPLICATION
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;


Loc::loadMessages(__FILE__);

$this->setFrameMode(true); // not need dynamic area
if (!$arResult['NEED_SHOW']) { return; }

$output = '';

try
{
	$output .= $arResult['ASSETS_HTML'];

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
	$selector = htmlspecialcharsback($arResult['PARAMS']['selector']);
	$position = $arResult['PARAMS']['position'];

	if (empty($selector))
	{
		$selector = '#' . $containerId . '-container';
		$position = 'afterbegin';
		$factoryOptions += [
			'preserve' => false,
		];

		$output .= <<<CONTENT
			<div id="{$containerId}-container" class="yandex-pay"></div>
CONTENT;
	}
	else
	{
		$factoryOptions += [
			'preserve' => true,
		];
	}

	$initScript = file_get_contents(__DIR__ . '/init.js');
	$widgetOptionsJson = Json::encode($widgetOptions);
	$factoryOptionsJson = Json::encode($factoryOptions);

	$output .= <<<CONTENT
		<script>
			(function() {
				{$initScript}
				function run() {
					const factory = new BX.YandexPay.Factory({$factoryOptionsJson});
					const selector = '{$selector}';
					const position = '{$position}';
	
					factory.inject(selector, position)
						.then((widget) => {
							widget.setOptions({$widgetOptionsJson});
							widget.cart();
						})
						.catch((error) => {
							console.warn(error);
						});
				}
			})();
		</script>
CONTENT;
}
catch (Main\SystemException $exception)
{
	if (!isset($USER) || !$USER->IsAdmin()) { return; }

	$output = $exception->getMessage(); // todo format
}

$arResult['OUTPUT'] = $output;