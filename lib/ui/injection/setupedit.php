<?php
namespace YandexPay\Pay\Ui\Injection;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay;

class SetupEdit extends Pay\Ui\Reference\Page
{
	use Concerns\HasMessage;

	public function show() : void
	{
		global $APPLICATION;

		$primary = $this->request->get('id') ?: null;
		$useCopy = ($this->request->get('copy') === 'Y');
		$isNew = ($primary === null || $useCopy);
		$baseQuery = [
			'lang' => LANGUAGE_ID,
		];
		
		$APPLICATION->IncludeComponent('yandexpay.pay:admin.form', '', [
			'FORM_ID' => 'YANDEX_PAY_ADMIN_INJECTION_ADD',
			'PROVIDER_CLASS_NAME' => Pay\Component\Model\Form::class,
			'DATA_CLASS_NAME' => Pay\Injection\Setup\RepositoryTable::class,
			'TITLE' => self::getMessage('TITLE'),
			'TITLE_ADD' => self::getMessage('TITLE_ADD'),
			'BTN_SAVE' => $isNew ? self::getMessage('BTN_ADD') : self::getMessage('BTN_SAVE'),
			'ALLOW_SAVE' => Pay\Ui\Access::hasRights($this->getWriteRights()),
			'LIST_URL' => Pay\Ui\Admin\Path::getModuleUrl('injection_grid', $baseQuery),
			'PRIMARY' => $primary,
			'COPY' => $useCopy,
			'CONTEXT_MENU' => [
				[
					'ICON' => 'btn_list',
					'LINK' => Pay\Ui\Admin\Path::getModuleUrl('injection_grid', $baseQuery),
					'TEXT' => self::getMessage('CONTEXT_MENU_LIST'),
				],
			],
			'TABS' => [
				[
					'name' => self::getMessage('TAB_COMMON'),
					'fields' => [
						'TRADING_ID',
						'BEHAVIOR',
					],
				],
			],
		]);
	}
}