<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use YandexPay\Pay;
use Bitrix\Main;

/** @var $component YandexPay\Pay\Components\AdminForm */
/** @var $field array */

$rowAttributes = [];
$fieldControl = $component->getFieldControl($field);
$hasDescription = isset($field['DESCRIPTION']);
$hasNote = isset($field['NOTE']) && !empty(trim($field['NOTE']));
$hasAdditionalRow = ($hasDescription || $hasNote);

if (isset($field['DEPEND']))
{
	Main\UI\Extension::load('yandexpaypay.admin.ui.input.dependfield');

	$rowAttributes['class'] = 'js-plugin';
	$rowAttributes['data-plugin'] = 'Ui.Input.DependField';
	$rowAttributes['data-depend'] = Main\Web\Json::encode($field['DEPEND'], JSON_UNESCAPED_UNICODE);

	if ($field['DEPEND_HIDDEN'])
	{
		$rowAttributes['class'] .= ' is--hidden';
	}
}

if (isset($field['INTRO']))
{
	?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%">&nbsp;</td>
		<td class="adm-detail-content-cell-r">
			<small><?= $field['INTRO']; ?></small>
		</td>
	</tr>
	<?php
}
?>
<tr <?= Pay\Ui\UserField\Helper\Attributes::stringify($rowAttributes); ?>>
	<td class="adm-detail-content-cell-l adm-detail-valign-<?= $fieldControl !== null && $fieldControl['VALIGN'] ? $fieldControl['VALIGN'] : 'middle'; ?> <?= $hasAdditionalRow ? 'pos-inner--bottom' : ''; ?>" width="40%">
		<?php
		include __DIR__ . '/field-title.php';
		?>
	</td>
	<td class="adm-detail-content-cell-r <?= $hasAdditionalRow ? 'pos-inner--bottom' : ''; ?>">
		<?= $fieldControl !== null ? $fieldControl['CONTROL'] : ''; ?>
	</td>
</tr>
<?php

if ($hasAdditionalRow)
{
	?>
	<tr>
		<td class="adm-detail-content-cell-l pos-inner--top" width="40%">&nbsp;</td>
		<td class="adm-detail-content-cell-r pos-inner--top">
			<?php
			if ($hasDescription)
			{
				echo '<small>' . $field['DESCRIPTION'] . '</small>';
			}

			if ($hasNote)
			{
				echo BeginNote();
				echo $field['NOTE'];
				echo EndNote();
			}
			?>
		</td>
	</tr>
	<?php
}