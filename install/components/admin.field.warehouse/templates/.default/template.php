<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main;
use YandexPay\Pay\Ui\UserField\Helper\Attributes;

/** @var array $arParams */

$fields = $arParams['~FIELDS'];
$values = $arParams['~VALUES'];
$settings = $arParams['~SETTINGS'];
$attributes = $settings['ATTRIBUTES'] ?? [];

$apiKey = Main\Config\Option::get('fileman', 'yandex_map_api_key', '');

CJSCore::Init();

$assets = Main\Page\Asset::getInstance();

$assets->addJs('/bitrix/js/sale/core_ui_widget.js');
$assets->addJs('/bitrix/js/sale/core_ui_etc.js');
$assets->addJs('/bitrix/js/sale/core_ui_autocomplete.js');

?>
<div <?= Attributes::stringify($attributes) ?>>
	<div class="js-plugin" data-plugin="Ui.Field.Warehouse" data-api-key="<?= htmlspecialcharsbx($apiKey) ?>">
		<div class="js-field-warehouse__map" style="500px; height: 500px; background: grey;"></div>
		<?php
		include __DIR__ . '/partials/suggest.php';
		?>
		<button type="button">Уточнить</button>
	</div>
	<div>
		<?php
		foreach ($fields as $field)
		{
			?>
			<div><?= $field['CONTROL'] ?></div>
			<?php
		}
		?>
	</div>
</div>
