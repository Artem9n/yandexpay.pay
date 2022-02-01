<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/**
 * @global $APPLICATION
 * @var $component \YandexPay\Pay\Components\AdminForm
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 */

if (!empty($arResult['CONTEXT_MENU']))
{
	$context = new CAdminContextMenu($arResult['CONTEXT_MENU']);
	$context->Show();
}

if ($component->hasErrors())
{
	$component->showErrors();
}

$tabControl = new \CAdminTabControl($arParams['FORM_ID'], $arResult['TABS'], false, true);

$formActionUri = !empty($arParams['FORM_ACTION_URI'])
	? $arParams['FORM_ACTION_URI']
	: htmlspecialcharsbx($APPLICATION->GetCurPageParam());

?>
<form method="POST" action="<?= $formActionUri; ?>" enctype="multipart/form-data">
	<?php
	if ($arParams['FORM_BEHAVIOR'] === 'steps')
	{
		?>
		<input type="hidden" name="STEP" value="<?= $arResult['STEP']; ?>" />
		<?php
	}

	$tabControl->Begin();

	echo bitrix_sessid_post();

	foreach ($arResult['TABS'] as $tab)
	{
		$tabControl->BeginNextTab([ 'showTitle' => false ]);

		$isActiveTab = ($arParams['FORM_BEHAVIOR'] !== 'steps' || $tab['STEP'] === $arResult['STEP']);
		$tabLayout = $tab['LAYOUT'] ?: 'default';
		$fields = $tab['FIELDS'];

		include __DIR__ . '/hidden.php';
		include __DIR__ . '/tab-' . $tabLayout . '.php';
	}

	$tabControl->Buttons();

	include __DIR__ . '/buttons.php';

	$tabControl->End();
	?>
</form>
<?php
if ($arParams['FORM_BEHAVIOR'] === 'steps')
{
	?>
	<script>
		<?php
		foreach ($arResult['TABS'] as $tab)
		{
			if ($tab['STEP'] !== $arResult['STEP'])
			{
				?>
				<?= $arParams['FORM_ID']; ?>.DisableTab('<?= $tab['DIV']; ?>');
				<?php
			}
		}
		?>
	</script>
	<?php
}
