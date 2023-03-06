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

	if (empty($arResult['SELECTOR']))
	{
		$arResult['SELECTOR'] = '#' . $arResult['CONTAINER_ID'] . '-container';
		$arResult['POSITION'] = 'afterbegin';
		$arResult['FACTORY_OPTIONS'] += [
			'preserve' => false,
		];

		$output .= <<<CONTENT
			<div id="{$arResult['CONTAINER_ID']}-container" class="yandex-pay"></div>
CONTENT;
	}
	else
	{
		$arResult['FACTORY_OPTIONS'] += [
			'preserve' => true,
		];
	}

	$initScript = file_get_contents(__DIR__ . '/init.js');
	$widgetOptionsJson = Json::encode($arResult['WIDGET_OPTIONS']);
	$factoryOptionsJson = Json::encode($arResult['FACTORY_OPTIONS']);

	$output .= <<<CONTENT
		<script>
			(function() {
				{$initScript}
				function run() {
					const factory = new BX.YandexPay.Factory({$factoryOptionsJson});
					const selector = '{$arResult['SELECTOR']}';
					const position = '{$arResult['POSITION']}';
	
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

	$message = str_replace("\n", "<br />", $exception->getMessage());

	$output .= <<<CONTENT
		<p style="color:red">$message</p>
CONTENT;
}

$arResult['OUTPUT'] = $output;

if (!$arParams['RETURN'])
{
	echo $arResult['OUTPUT'];
}