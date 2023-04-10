<?php
namespace YandexPay\Pay\Ui\SaleInput;

use Bitrix\Main;
use Bitrix\Sale\Internals;
use YandexPay\Pay;
use YandexPay\Pay\Ui;
use YandexPay\Pay\Reference\Concerns;

if (!Main\Loader::includeModule('sale')) { return; }

class DisplayType extends Internals\Input\StringInput
{
	use Concerns\HasMessage;

	public static function getEditHtmlSingle($name, array $input, $value) : string
	{
		$value = ($value === null ?  [] : unserialize($value, [ 'allowed_classes' => false ]));

		$fieldsDisplay = Pay\Injection\Behavior\AbstractBehavior::getDisplayFields();

		foreach ($fieldsDisplay as $fieldCode => &$fields)
		{
			if ($fieldCode === 'DISPLAY')
			{
				unset($fields['SETTINGS']);
			}

			unset($fields['GROUP']);
		}
		unset($fields);

		$userFields = [
			'NAME' => self::getMessage('NAME'),
			'FIELDS' => $fieldsDisplay,
			'SETTINGS' => [
				'SUMMARY' => '#DISPLAY#, #VARIANT_BUTTON#, #WIDTH_BUTTON#, #BUTTON_THEME_WIDGET#, #TYPE_WIDGET#',
				'LAYOUT' => 'summary',
				'CLASS' => 'sale-yapay-display',
			],
		];

		$control = [
			'NAME' => $name,
			'VALUE' => $value + [
				'DISPLAY' => Pay\Injection\Behavior\Display\Registry::BUTTON,
				'WIDTH_BUTTON' => 'MAX',
			],
		];

		return Ui\UserField\FieldsetType::GetEditFormHtml($userFields, $control);
	}
}