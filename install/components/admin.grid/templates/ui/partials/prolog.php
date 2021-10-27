<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main;

/** @var \CBitrixComponentTemplate $this */
/** @var \YandexPay\Pay\Components\AdminGrid $component */
/** @var \CAdminUiList $adminList
 * @var array $arResult
 * @var string $templateFolder
 */

$adminList->BeginPrologContent();

if ($arResult['REDIRECT'] !== null)
{
	?>
	<script>
		window.top.location = <?= Main\Web\Json::encode($arResult['REDIRECT']); ?>;
	</script>
	<?php
}

if (!empty($arResult['LIST_EXTENSION']))
{
	$this->addExternalJs($templateFolder . '/scripts/listextension.js');

	$extensionParameters =
		[ 'grid' => $adminList->table_id ]
		+ $arResult['LIST_EXTENSION'];

	?>
	<script>
		BX.ready(BX.defer(function() {
			const options = <?= Main\Web\Json::encode($extensionParameters) ?>;
			const AdminList = BX.namespace('YandexMarket.AdminList');

			new AdminList.ListExtension(options);
		}));
	</script>
	<?php
}

if ($component->hasErrors())
{
	foreach ($component->getErrors() as $message)
	{
		$adminList->AddUpdateError($message);
	}
}

if ($component->hasWarnings())
{
	$component->showWarnings();
}

$adminList->EndPrologContent();

$prologContent = $adminList->sPrologContent;

AddEventHandler('main', 'onAfterAjaxResponse', static function() use ($prologContent) {
	echo $prologContent;
});