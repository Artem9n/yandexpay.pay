<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/** @var $component YandexPay\Pay\Components\AdminForm */

if (empty($tab['HIDDEN'])) { return; }

?>
<tr>
	<td class="b-form-hidden-row" colspan="2">
		<?php
		foreach ($tab['HIDDEN'] as $fieldKey)
		{
			$field = $component->getField($fieldKey);
			$fieldValue = $component->getFieldValue($field);

			if (is_array($fieldValue))
			{
				foreach ($fieldValue as $key => $value)
				{
					?>
					<input type="hidden" name="<?= $field['FIELD_NAME'] . '[' . $key . ']'; ?>" value="<?= htmlspecialcharsbx($value); ?>" />
					<?php
				}
			}
			else
			{
				?>
				<input type="hidden" name="<?= $field['FIELD_NAME']; ?>" value="<?= htmlspecialcharsbx($fieldValue); ?>" />
				<?php
			}
		}
		?>
	</td>
</tr>