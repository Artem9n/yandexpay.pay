<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Ui\UserField\Helper\Attributes;

/** @var array $arResult */

?>
<div class="bx-sls js-field-warehouse__suggest">
	<div class="dropdown-block bx-ui-yapay-suggest-input-block">
		<span class="dropdown-icon"></span>
		<?php
		if (isset($fields['FULL_ADDRESS']))
		{
			echo Attributes::insert($fields['FULL_ADDRESS']['CONTROL'], [
				'class' => 'dropdown-field',
				'autocomplete' => 'off',
				'placeholder' => Loc::getMessage('YAPAY_FIELD_WAREHOUSE_SUGGEST_PLACEHOLDER')
			]);
		}
		else
		{
			?>
			<input type="text" autocomplete="off" class="dropdown-field" />
			<?php
		}
		?>
		<div class="dropdown-fade2white"></div>
		<div class="bx-ui-yapay-suggest-loader"></div>
		<div class="bx-ui-yapay-suggest-clear" title=""></div>
		<div class="bx-ui-yapay-suggest-pane"></div>
	</div>
	<script type="text/html" data-template-id="bx-ui-yapay-suggest-error">
		<div class="bx-ui-yapay-suggest-error">
			<div></div>
			{{message}}
		</div>
	</script>

	<script type="text/html" data-template-id="bx-ui-yapay-suggest-dropdown-item">
		<div class="dropdown-item bx-ui-yapay-suggest-variant">
			<span class="dropdown-item-text">{{display_wrapped}}</span>
		</div>
	</script>
</div>

