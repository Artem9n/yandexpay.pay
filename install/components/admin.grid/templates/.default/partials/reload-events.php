<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main\Web\Json;

if (empty($arParams['RELOAD_EVENTS'])) { return; }

$events = (array)$arParams['RELOAD_EVENTS'];

?>
<script>
	(function() {
		const events = <?= Json::encode($events) ?>;
		const gridId = '<?= $arParams['GRID_ID'] ?>';
		const ajaxUrl = <?= Json::encode($arParams['AJAX_URL']) ?>;

		for (const event of events) {
			BX.addCustomEvent(event, function() {
				if (window[gridId] == null) { return; }

				setTimeout(() => {
					window[gridId].GetAdminList(ajaxUrl || window.location.href);
				}, 500);
			});
		}
	})();
</script>