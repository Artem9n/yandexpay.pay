<?php
namespace YandexPay\Pay\Ui\UserField;

use YandexPay\Pay;
use YandexPay\Pay\Reference\Concerns;

class LogAuditType extends EnumerationType
{
	use Concerns\HasMessage;

	protected static $optionCache;

	public static function getAdminListViewHTML($arUserField, $arHtmlControl) : string
	{
		$arUserField['VALUE'] = Pay\Logger\Audit::getTitle($arUserField['VALUE']);
		$result = parent::getAdminListViewHTML($arUserField, $arUserField);
		return $result;
	}
}