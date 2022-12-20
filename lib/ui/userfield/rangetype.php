<?php

namespace YandexPay\Pay\Ui\UserField;

use Bitrix\Main;

class RangeType extends StringType
{
	protected static function getEditInput($userField, $htmlControl, array $attributes = []) : string
	{
		Main\UI\Extension::load('yandexpaypay.admin.ui.rangetype');

		$attributes += [
			'type' => 'range',
			'name' => $htmlControl['NAME'],
		];

		$attributes += array_filter([
			'min' => isset($userField['SETTINGS']['MIN']) ? (int)$userField['SETTINGS']['MIN'] : 0,
			'max' => isset($userField['SETTINGS']['MAX']) ? (int)$userField['SETTINGS']['MAX'] : 100,
			'step' => isset($userField['SETTINGS']['STEP']) ? (int)$userField['SETTINGS']['STEP'] : null,
			'data-plugin' => 'Ui.RangeType',
			'class' => 'js-plugin',
		]);

		$value = (string)$htmlControl['VALUE'] !== '' ? $htmlControl['VALUE'] : $userField['SETTINGS']['DEFAULT_VALUE'] ?? '';

		return sprintf(
			'<div class="b-range-container"><input %s value="%s" /><span>%s px</span></div>',
			Helper\Attributes::stringify($attributes),
			$value,
			$value
		);
	}
}