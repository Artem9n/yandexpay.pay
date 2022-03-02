<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/**
 * @var array $arResult
 */

use Bitrix\Main\Web\Json;

if (!empty($arResult['ERRORS']))
{
	if (!isset($USER) || !$USER->IsAdmin()) { return; }

	ShowError($arResult['ERRORS']);
	return;
}

if (!empty($arResult['PARAMS']['selector']))
{
	foreach ($arResult['preload'] as $path)
	{
		$arResult['CONTENT'] .= sprintf('<script src="%s"></script>', $path);
	}

	$arResult['CONTENT'] .= sprintf(
		"<script>
			(function() {
				const factory = new BX.YandexPay.Factory(%s);
		
				factory.assets(%s)
					.then(() => {
						factory.bootSolution();
						return factory.inject('%s', '%s');
					})
					.then((widget) => {
						widget.setOptions(%s);
						widget.cart();
					})
					.catch((error) => {
						console.warn(error);
					});
			})();
		</script>",
		Json::encode($arResult['factory_options']),
		Json::encode($arResult['async']),
		htmlspecialcharsback($arResult['PARAMS']['selector']),
		$arResult['PARAMS']['position'],
		Json::encode($arResult['widget_options'])
	);
}
else // inline
{
	foreach ($arResult['preload'] as $path)
	{
		echo sprintf('<script src="%s"></script>', $path);
	}

	?>
	<div id="yandexpay" class="yandex-pay"></div>
	<script>
		(function() {
			const factory = new BX.YandexPay.Factory(<?= Json::encode($arResult['factory_options']) ?>);

			factory.assets(<?= Json::encode($arResult['async']) ?>)
				.then(() => {
					factory.bootSolution();

					return document.getElementById('yandexpay');
				})
				.then((element) => {
					const widget = factory.install(element);

					widget.setOptions(<?= Json::encode($arResult['widget_options']) ?>);
					widget.cart();
				});
		})();
	</script>
	<?php
}
