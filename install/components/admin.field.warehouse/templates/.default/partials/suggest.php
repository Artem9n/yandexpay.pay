<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

//sale/install/components/bitrix/sale.location.selector.search/templates/.default/template.php

?>
<div class="js-field-warehouse__suggest">
	<div class="dropdown-block bx-ui-sls-input-block">

		<span class="dropdown-icon"></span>
		<input type="text" autocomplete="off" class="dropdown-field" />

		<div class="dropdown-fade2white"></div>
		<div class="bx-ui-sls-loader"></div>
		<div class="bx-ui-sls-clear" title=""></div>
		<div class="bx-ui-sls-pane"></div>
	</div>
	<script type="text/html" data-template-id="bx-ui-sls-error">
		<div class="bx-ui-sls-error">
			<div></div>
			{{message}}
		</div>
	</script>

	<script type="text/html" data-template-id="bx-ui-sls-dropdown-item">
		<div class="dropdown-item bx-ui-sls-variant">
			<span class="dropdown-item-text">{{display_wrapped}}</span>
		</div>
	</script>
</div>

