<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

use YandexPay\Pay\Injection\Solution;
use Bitrix\Main;
use Bitrix\Main\UI\Extension;

/** @var CMain $APPLICATION */

$arResult['ASSETS_HTML'] = '';
$partials = [];

if ($APPLICATION->GetPageProperty('yandexpay_extension_widget') !== 'Y')
{
	$APPLICATION->SetPageProperty('yandexpay_extension_widget', 'Y');
	$partials['yandexpaypay.widget'] = Extension::getAssets('yandexpaypay.widget');
}

if (
	!empty($arResult['PARAMS']['solution'])
	&& $APPLICATION->GetPageProperty('yandexpay_extension_' . $arResult['PARAMS']['solution']) !== 'Y'
)
{
	$APPLICATION->SetPageProperty('yandexpay_extension_' . $arResult['PARAMS']['solution'], 'Y');
	$solution = Solution\Registry::getInstance($arResult['PARAMS']['solution']);
	$partials[$arResult['PARAMS']['solution']] = $solution->getAssets();
}

foreach ($partials as $assetsType => $assets)
{
	$inline = array_intersect_key($assets, [
		'css' => true,
	]);
	$external = array_diff_key($assets, $inline);

	foreach ($inline as $type => $resources)
	{
		foreach ($resources as $path)
		{
			if (CMain::IsExternalLink($path))
			{
				if (!isset($external[$type])) { $external[$type] = []; }

				$external[$type][] = $path;
			}
			else
			{
				$url = Main\Page\Asset::getInstance()->getFullAssetPath($path);
				[$fullPath, $query] = explode('?', $url);

				$absolutePath = Main\IO\Path::convertSiteRelativeToAbsolute($fullPath);

				$content = file_get_contents($absolutePath);

				if ($content === false)
				{
					if (!isset($external[$type])) { $external[$type] = []; }

					$external[$type][] = $path;
				}
				else
				{
					$arResult['ASSETS_HTML'] .= sprintf('<style>%s</style>', $content);
				}
			}
		}
	}

	if (!empty($external))
	{
		$external['skip_core'] = true;

		Extension::registerAssets('yandexpaypay_component_external_' . $assetsType, $external);

		$arResult['ASSETS_HTML'] .= Extension::getHtml('yandexpaypay_component_external_' . $assetsType);
	}
}
