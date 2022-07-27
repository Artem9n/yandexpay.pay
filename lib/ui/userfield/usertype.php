<?php

namespace YandexPay\Pay\Ui\UserField;

use Bitrix\Main;

class UserType
{
	public static function getEditFormHTML($userField, $htmlControl) : string
	{
		Main\UI\Extension::load('yandexpaypay.admin.ui.usertype');

		$name = htmlspecialcharsbx($htmlControl['NAME']);
		$value = htmlspecialcharsbx($htmlControl['VALUE']);
		$lang = LANGUAGE_ID;

		return <<<OUTPUT
			<input type="text" name="{$name}" id="{$name}" value="{$value}" size="5">
			<button class="adm-btn js-plugin" type="button" data-plugin="Ui.UserType" data-url="/bitrix/admin/user_search.php?lang={$lang}">...</button>
OUTPUT;
	}
}