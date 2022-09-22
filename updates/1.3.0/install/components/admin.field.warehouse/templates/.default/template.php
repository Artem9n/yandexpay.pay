<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
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

$messages = [
	'YAPAY_FIELD_WAREHOUSE_SUGGEST_ERROR' => Loc::getMessage('YAPAY_FIELD_WAREHOUSE_SUGGEST_ERROR'),
	'YAPAY_FIELD_WAREHOUSE_SUGGEST_MAP_ERROR' => Loc::getMessage('YAPAY_FIELD_WAREHOUSE_SUGGEST_MAP_ERROR'),
	'YAPAY_FIELD_WAREHOUSE_CANT_LOAD_MAPS' => Loc::getMessage('YAPAY_FIELD_WAREHOUSE_CANT_LOAD_MAPS'),
	'YAPAY_FIELD_WAREHOUSE_SUGGEST_NOTHING_FOUND' => Loc::getMessage('YAPAY_FIELD_WAREHOUSE_SUGGEST_NOTHING_FOUND'),
	'YAPAY_FIELD_WAREHOUSE_MAPS_API_KEY_NOT_FOUND' => Loc::getMessage('YAPAY_FIELD_WAREHOUSE_MAPS_API_KEY_NOT_FOUND'),
];

?>
<div <?= Attributes::stringify($attributes) ?>>
	<div class="bx-yapay-warehouse-layout js-plugin" data-plugin="Ui.Field.Warehouse" data-api-key="<?= htmlspecialcharsbx($apiKey) ?>">

		<div class="bx-yapay-warehouse-layout__map js-field-warehouse__map"></div>
		<div class="bx-yapay-warehouse-layout__panel">
			<div class="bx-yapay-warehouse-header">
				<div class="bx-yapay-warehouse-error js-field-warehouse__error">

				</div>
				<div class="bx-yapay-warehouse-suggest">
					<?php
					include __DIR__ . '/partials/suggest.php';
					?>
				</div>
				<a href="javascript:void()" class="bx-yapay-warehouse-clarify js-field-warehouse__clarify" type="button"
				   data-alt="<?= Loc::getMessage('YAPAY_FIELD_WAREHOUSE_DETAILS_BTN_CLOSE') ?>">
					<?= Loc::getMessage('YAPAY_FIELD_WAREHOUSE_DETAILS_BTN_OPEN') ?>
				</a>
			</div>
			<div class="bx-yapay-warehouse-details js-field-warehouse__details readonly">
				<?php
				foreach ($fields as $code => $field)
				{
					if ($code === 'FULL_ADDRESS') { continue; }

					if ($field['MANDATORY'] === 'Y')
					{
						$field['TITLE'] = sprintf('<strong>%s</strong>', $field['TITLE']);
					}

					?>
					<div class="bx-yapay-warehouse-detail">
						<div class="bx-yapay-warehouse-detail__label"><?= $field['TITLE'] ?></div>
						<div class="bx-yapay-warehouse-detail__control"><?= Attributes::insert($field['CONTROL'], [
							'class' => 'bx-yapay-warehouse-detail__input',
							'readonly' => true,
						]) ?></div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
<script>
	BX.message(<?= Main\Web\Json::encode($messages); ?>);
</script>
