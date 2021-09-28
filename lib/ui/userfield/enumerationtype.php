<?php
namespace YandexPay\Pay\Ui\Userfield;

use Bitrix\Main;

class EnumerationType extends Main\UserField\Types\EnumType
{
	public static function getList(array $userField) : \CDBResult
	{
		$result = new \CDBResult();
		$result->InitFromArray((array)$userField['VALUES']);

		return $result;
	}

	public static function getEditFormHtml(array $userField, ?array $additionalParameters) : string
	{
		if (!isset($userField['SETTINGS']['DISPLAY']))
		{
			$userField['SETTINGS']['DISPLAY'] = static::DISPLAY_LIST;
		}

		return parent::getEditFormHtml($userField, $additionalParameters);
	}
}