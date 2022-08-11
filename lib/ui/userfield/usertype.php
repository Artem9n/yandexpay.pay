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
		$userData = static::getUserData($value);
		$login = $userData['LOGIN'] ?? '';
		$firstName = $userData['FIRST_NAME'] ?? '';
		$lastName = $userData['LAST_NAME'] ?? '';

		return <<<OUTPUT
			<div class="yapay-user-type">
				<input type="text" name="{$name}" id="{$name}" value="{$value}" size="5">
				<button class="adm-btn js-plugin" type="button" data-plugin="Ui.UserType" data-url="/bitrix/admin/user_search.php?lang={$lang}">...</button>
				<span class="yapay-user-data">
					[<a target="_blank" class="tablebodylink" href="/bitrix/admin/user_edit.php?ID={$value}&lang={$lang}">{$value}</a>] ({$login}) {$firstName} {$lastName}
				</span>
			</div>
OUTPUT;
	}

	protected static function getUserData(string $id) : array
	{
		$result = [];

		$query = Main\UserTable::getList([
			'filter' => [ '=ID' => $id ],
			'select' => [ 'LOGIN', 'NAME', 'LAST_NAME' ],
			'limit' => 1,
		]);

		if ($user = $query->fetch())
		{
			$result = [
				'LOGIN' => htmlspecialcharsbx($user['LOGIN']),
				'FIRST_NAME' => htmlspecialcharsbx($user['NAME']),
				'LAST_NAME' => htmlspecialcharsbx($user['LAST_NAME'])
			];
		}

		return $result;
	}
}