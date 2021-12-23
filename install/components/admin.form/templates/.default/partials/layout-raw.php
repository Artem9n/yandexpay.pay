<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

use Bitrix\Main\Web\Json;

/** @var $component \YandexPay\Pay\Components\AdminForm */

$formActionUri = !empty($arParams['FORM_ACTION_URI'])
	? $arParams['FORM_ACTION_URI']
	: htmlspecialcharsbx($APPLICATION->GetCurPageParam());

if ($component->hasErrors())
{
	$component->showErrors();
}

if ($arResult['SUCCESS'])
{
	$data = [
		'action' => $arResult['ACTION'],
		'primary' => $arResult['PRIMARY'],
		'data' => $arResult['ITEM'],
	];

	if (!empty($arParams['NEXT_URL']))
	{
		$data['next'] = [
			'url' => str_replace('#ID#', $arResult['PRIMARY'], $arParams['NEXT_URL']),
		];

		if (!empty($arParams['NEXT_PARAMETERS']))
		{
			$data['next'] += $arParams['NEXT_PARAMETERS'];
		}
	}

	?>
	<script>
		top.BX.onCustomEvent('yapayFormSave', [<?= Json::encode($data) ?>]);
	</script>
	<?php

	if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1) { die(); }
}

$arResult['DISABLE_REQUIRED_HIGHLIGHT'] = true;

?>
<form class="yandex-pay-admin-form" method="POST" action="<?= $formActionUri; ?>" enctype="multipart/form-data">
	<?php
	echo bitrix_sessid_post();
	?>
	<table class="edit-table" width="100%">
		<?php
		foreach ($arResult['TABS'] as $tab)
		{
			$isActiveTab = true;
			$fields = $tab['FIELDS'];

			include __DIR__ . '/hidden.php';
			include __DIR__ . '/tab-default.php';
		}
		?>
	</table>
</form>