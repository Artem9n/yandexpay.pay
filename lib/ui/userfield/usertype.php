<?php

namespace YandexPay\Pay\Ui\UserField;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;

class UserType
{
	use Concerns\HasMessage;

	public const USER_TYPE_ID = 'yapay_user';

	public function getUserTypeDescription() : array
	{
		return [
			'DESCRIPTION' => static::getMessage('NAME'),
			'USER_TYPE_ID' => static::USER_TYPE_ID,
			'CLASS_NAME' => static::class,
			'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_INT,
		];
	}

	public static function getEditFormHTML($userField, $htmlControl) : string
	{
		Main\UI\Extension::load('yandexpaypay.admin.ui.usertype');

		$name = htmlspecialcharsbx($htmlControl['NAME']);
		$value = htmlspecialcharsbx($htmlControl['VALUE']);
		$lang = LANGUAGE_ID;
		$userData = static::getUserData($value);
		$userHtml = '';
		$login = $userData['LOGIN'] ?? '';
		$firstName = $userData['FIRST_NAME'] ?? '';
		$lastName = $userData['LAST_NAME'] ?? '';

		if (!empty($userData))
		{
			$userHtml = sprintf('[<a target="_blank" class="tablebodylink" href="/bitrix/admin/user_edit.php?ID=%s&lang=%s">%s</a>] (%s) %s %s',
				$value,
				$lang,
				$value,
				$login,
				$firstName,
				$lastName
			);
		}

		return <<<OUTPUT
			<div class="yapay-user-type">
				<input class="yapay-user-type-item" type="text" name="{$name}" id="{$name}" value="{$value}" size="5">
				<button class="yapay-user-type-item adm-btn js-plugin" type="button" data-plugin="Ui.UserType" data-url="/bitrix/admin/user_search.php?lang={$lang}">...</button>
				<span class="yapay-user-type-item yapay-user-data">{$userHtml}</span>
			</div>
OUTPUT;
	}

	/** @noinspection PhpUnusedParameterInspection */
	public function getDBColumnType(array $userField) : string
	{
		return 'int';
	}

	protected static function getUserData(string $id) : array
	{
		if (empty($id)) { return []; }

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