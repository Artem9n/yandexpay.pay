<?php
namespace YandexPay\Pay\Ui\Trading;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay;

class Settings extends Pay\Ui\Reference\Page
{
	use Concerns\HasMessage;

	protected $layout;

	public function show() : void
	{
		global $APPLICATION;

		Main\UI\Extension::load('yandexpaypay.admin.ui');

		$primary = $this->request->get('id') ?: null;
		$baseQuery = [
			'lang' => LANGUAGE_ID,
		];

		$APPLICATION->IncludeComponent('yandexpay.pay:admin.form', '', [
			'LAYOUT' => $this->getLayout(),
			'FORM_ID' => 'YANDEX_PAY_ADMIN_TRADING_ADD',
			'PROVIDER_CLASS_NAME' => Pay\Component\Trading\Settings\Form::class,
			'DATA_CLASS_NAME' => Pay\Trading\Setup\RepositoryTable::class,
			'TITLE' => self::getMessage('TITLE'),
			'ALLOW_SAVE' => Pay\Ui\Access::hasRights($this->getWriteRights()),
			'LIST_URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_grid', $baseQuery),
			'SAVE_URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_setup', $baseQuery),
			'PRIMARY' => $primary,
			'CONTEXT_MENU' => [
				[
					'ICON' => 'btn_list',
					'LINK' => Pay\Ui\Admin\Path::getModuleUrl('trading_grid', $baseQuery),
					'TEXT' => self::getMessage('CONTEXT_MENU_LIST'),
				],
			],
			'BUTTONS' => [
				[
					'BEHAVIOR' => 'save',
					'NAME' => self::getMessage('SAVE_BUTTON'),
				],
				[
					'NAME' => self::getMessage('RESET_BUTTON'),
					'ATTRIBUTES' => [
						'name' => 'postAction',
						'value' => 'reset',
						'onclick' => sprintf(
							'if (!confirm("%s")) { return false; }',
							addslashes(self::getMessage('RESET_CONFIRM'))
						)
					],
				],
			],
		]);
	}

	public function getLayout() : ?string
	{
		return $this->layout;
	}

	public function setLayout(string $layout) : void
	{
		$this->layout = $layout;
	}
}