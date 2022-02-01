<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

use Bitrix\Main;
use YandexPay\Pay;

/** @var Pay\Components\AdminGrid $component
 * @var array $arResult
 * @var array $arParams
 */

$adminList = $component->getViewList();

$adminList->BeginPrologContent();

if ($arResult['REDIRECT'] !== null)
{
	?>
	<script>
		window.top.location = <?= Main\Web\Json::encode($arResult['REDIRECT']); ?>;
	</script>
	<?php
}

if ($component->hasErrors())
{
	$component->showErrors();
}

if ($component->hasWarnings())
{
	$component->showWarnings();
}

$adminList->EndPrologContent();

$adminList->CheckListMode();

include __DIR__ . '/partials/reload-events.php';

if ($arParams['USE_FILTER'])
{
	include __DIR__ . '/partials/filter.php';
}

if ($adminList instanceof CAdminSubList)
{
	include __DIR__ . '/partials/display-sublist.php';
}
else
{
	$adminList->DisplayList();
}