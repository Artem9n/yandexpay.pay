<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/**
 * @var array $arResult
 */

use Bitrix\Main;
use YandexPay\Pay\Injection\Solution;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

try
{
	$arResult['core'] = Extension::getAssets('yandexpaypay.widget');
	$arResult['preload'] = $arResult['core']['js'];
	$arResult['async'] = array_diff_key($arResult['core'], [ 'js' => true ]);
	$arResult['async'] = array_merge([], ...array_values($arResult['async']));

	if (!empty($arResult['PARAMS']['solution']))
	{
		$solution = Solution\Registry::getInstance($arResult['PARAMS']['solution']);
		$arResult['async'] = array_merge($arResult['async'], ...array_values($solution->getExtensionFiles()));
	}

	$arResult['widget_options'] = array_diff_key($arResult['PARAMS'], [ 'order' => true , 'selector' => true, 'position' => true]);
	$arResult['factory_options'] = array_intersect_key($arResult['PARAMS'], [
		'solution' => true,
		'mode' => true,
		'buttonWidth' => true,
	]);
	$arResult['factory_options']['label'] = GetMessage('YANDEXPAY_BUTTON_LABEL');
	$order = $arResult['PARAMS']['order'];
	$selector = $arResult['PARAMS']['selector'];
	$position = $arResult['PARAMS']['position'];

	// apply mtime and min for assets

	foreach ([$arResult['preload'], $arResult['async']] as &$assets)
	{
		foreach ($assets as &$path)
		{
			if (CMain::IsExternalLink($path)) { continue; }

			$originalFile = Main\IO\Path::convertRelativeToAbsolute($path);
			$originalTime = filemtime($originalFile);

			if (Main\Page\Asset::canUseMinifiedAssets())
			{
				$minifiedFile = preg_replace('/\.(js|css)$/', '.min.$1', $originalFile);
				$minifiedTime = filemtime($minifiedFile);

				if ($minifiedTime !== false && $minifiedTime >= $originalTime)
				{
					$path = preg_replace('/\.(js|css)$/', '.min.$1', $path);
					$originalTime = $minifiedTime;
				}
			}

			if ($originalTime !== false)
			{
				$path .= (mb_strpos($path, '?') === false ? '?' : '&') . $originalTime;
			}
		}
		unset($path);
	}
	unset($assets);

}
catch (Main\SystemException $exception)
{
	$arResult['ERRORS'] = $exception->getMessage();
}