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
	// assets widget

	if ($APPLICATION->GetPageProperty('yandexpay_extension_widget') !== 'Y')
	{
		$APPLICATION->SetPageProperty('yandexpay_extension_widget', 'Y');
		echo Extension::getHtml('yandexpaypay.widget');
	}

	// assets solution

	if (
		!empty($arResult['PARAMS']['solution'])
		&& $APPLICATION->GetPageProperty('yandexpay_extension_' . $arResult['PARAMS']['solution']) !== 'Y'
	)
	{
		$APPLICATION->SetPageProperty('yandexpay_extension_' . $arResult['PARAMS']['solution'], 'Y');

		$solution = Solution\Registry::getInstance($arResult['PARAMS']['solution']);
		echo $solution->getExtension();
	}

	// assets expert js

	if ($arResult['JS_CONTENT'] !== null)
	{
		Main\Page\Asset::getInstance()->addString(sprintf('<script>%s</script>', $arResult['JS_CONTENT']));
	}

	// assets expert css

	if ($arResult['CSS_CONTENT'] !== null)
	{
		Main\Page\Asset::getInstance()->addString(sprintf('<style>%s</style>', $arResult['CSS_CONTENT']));
	}

	if (empty($arResult['SELECTOR']))
	{
		$arResult['SELECTOR'] = '#' . $arResult['CONTAINER_ID'] . '-container';
		$arResult['POSITION'] = 'afterbegin';
		$arResult['FACTORY_OPTIONS'] += [
			'preserve' => false,
		];

		?>
		<div id="<?= $arResult['CONTAINER_ID'] . '-container' ?>" class="yandex-pay"></div>
		<?php
	}
	?>
	<script>
		(function() {
			<?php
			echo file_get_contents(__DIR__ . '/init.min.js');
			?>
			function run() {
				const factory = new BX.YandexPay.Factory(<?= Json::encode($arResult['FACTORY_OPTIONS']) ?>);
				const selector = '<?= htmlspecialcharsback($arResult['SELECTOR']) ?>';
				const position = '<?= $arResult['POSITION']?>';

				factory.inject(selector, position)
					.then((widget) => {
						widget.setOptions(<?= Json::encode($arResult['WIDGET_OPTIONS']) ?>);
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
