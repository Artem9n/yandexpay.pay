<?php
namespace YandexPay\Pay\Ui\Trading;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay;

class SetupGrid extends Pay\Ui\Reference\Page
{
	use Concerns\HasMessage;

	public function show() : void
	{
		global $APPLICATION;

		$baseQuery = [
			'lang' => LANGUAGE_ID,
		];

		$APPLICATION->IncludeComponent('yandexpay.pay:admin.grid', '', [
			'GRID_ID' => 'YANDEX_PAY_TRADING_SETUP_GRID',
			'ALLOW_SAVE' => Pay\Ui\Access::hasRights($this->getReadRights()),
			'PROVIDER_CLASS_NAME' => Pay\Component\Model\Grid::class,
			'DATA_CLASS_NAME' => Pay\Trading\Setup\RepositoryTable::class,
			'EDIT_URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_edit', $baseQuery) . '&id=#ID#',
			'ADD_URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_edit', $baseQuery),
			'TITLE' => self::getMessage('TITLE'),
			'NAV_TITLE' => self::getMessage('NAVIGATION'),
			'LIST_FIELDS' => [
				'ID',
				'SITE_ID',
				'PERSON_TYPE_ID',
			],
			'CONTEXT_MENU' => [
				[
					'TEXT' => self::getMessage('ACTION_ADD'),
					'LINK' => Pay\Ui\Admin\Path::getModuleUrl('trading_edit', $baseQuery),
					'ICON' => 'btn_new',
				],
			],
			'ROW_ACTIONS' => [
				'SETUP' => [
					'URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_setup', $baseQuery) . '&id=#ID#',
					'ICON' => 'setting',
					'TEXT' => self::getMessage('ACTION_SETUP'),
				],
				'EDIT' => [
					'URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_edit', $baseQuery) . '&id=#ID#',
					'ICON' => 'edit',
					'TEXT' => self::getMessage('ACTION_EDIT'),
					'DEFAULT' => true
				],
				'COPY' => [
					'URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_edit', $baseQuery) . '&id=#ID#&copy=Y',
					'ICON' => 'copy',
					'TEXT' => self::getMessage('ACTION_COPY')
				],
				'ACTIVATE' => [
					'ACTION' => 'activate',
					'TEXT' => self::getMessage('ACTION_ACTIVATE')
				],
				'DEACTIVATE' => [
					'ACTION' => 'deactivate',
					'TEXT' => self::getMessage('ACTION_DEACTIVATE'),
					'CONFIRM' => 'Y',
					'CONFIRM_MESSAGE' => self::getMessage('ACTION_DEACTIVATE_CONFIRM')
				],
				'DELETE' => [
					'ICON' => 'delete',
					'TEXT' => self::getMessage('ACTION_DELETE'),
					'CONFIRM' => 'Y',
					'CONFIRM_MESSAGE' => self::getMessage('ACTION_DELETE_CONFIRM')
				],
			],
			'GROUP_ACTIONS' => [
				'activate' => self::getMessage('ACTION_ACTIVATE'),
				'deactivate' => self::getMessage('ACTION_DEACTIVATE'),
			],
		]);
	}
}