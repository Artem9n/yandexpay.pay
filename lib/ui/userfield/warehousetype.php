<?php
namespace YandexPay\Pay\Ui\UserField;

/** @noinspection PhpUnused */
class WarehouseType extends FieldsetType
{
	protected static function makeLayout($userField, $htmlControl) : Fieldset\AbstractLayout
	{
		$fields = static::getFields($userField);

		$result = new Fieldset\SummaryLayout($userField, $htmlControl['NAME'], $fields);
		$result->formRenderer(static function(array $fields, array $values, array $settings = []) {
			global $APPLICATION;

			ob_start();

			$APPLICATION->IncludeComponent('yandexpay.pay:admin.field.warehouse', '', [
				'FIELDS' => $fields,
				'VALUES' => $values,
				'SETTINGS' => $settings,
			]);

			return ob_get_clean();
		});

		return $result;
	}
}