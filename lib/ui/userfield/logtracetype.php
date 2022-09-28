<?php
namespace YandexPay\Pay\Ui\UserField;

use YandexPay\Pay;
use YandexPay\Pay\Reference\Concerns;

class LogTraceType extends EnumerationType
{
	use Concerns\HasMessage;

	protected static $loggerCache;

	public static function getAdminListViewHTML($arUserField, $arHtmlControl) : string
	{
		echo '<pre>';
		print_r($arUserField);
		echo '</pre>';

		return parent::getAdminListViewHTML($arUserField, $arHtmlControl);
	}
}