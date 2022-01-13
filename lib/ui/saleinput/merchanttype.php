<?php
namespace YandexPay\Pay\Ui\SaleInput;

use Bitrix\Main;
use Bitrix\Sale\Internals;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Ui;

if (!Main\Loader::includeModule('sale')) { return; }

class MerchantType extends Internals\Input\StringInput
{
	use Concerns\HasMessage;

	public static function getEditHtmlSingle($name, array $input, $value)
	{
		Main\UI\Extension::load('yandexpaypay.admin.ui.merchantbutton');

		$result = parent::getEditHtmlSingle($name, $input, $value);
		$result .= static::getButtonHtml();

		return $result;
	}

	protected static function getButtonHtml() : string
	{
		$attributes = [
			'type' => 'button',
			'value' => self::getMessage('INPUT_NAME'),
			'class' => 'adm-btn-green js-plugin-click',
			'data-plugin' => 'Ui.ButtonField',
			'data-form-url' => Ui\Admin\Path::getModuleUrl('trading_merchant', ['lang' => LANGUAGE_ID, 'view' => 'dialog']),
			'data-form-title' => self::getMessage('MODAL_TITLE'),
			'data-form-save-title' => self::getMessage('BTN_SAVE')
		];

		return sprintf('<input %s>', Ui\UserField\Helper\Attributes::stringify($attributes));
	}
}