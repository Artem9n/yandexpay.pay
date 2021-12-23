<?php
namespace YandexPay\Pay\Ui\Trading;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay;

class SetupEdit extends Pay\Ui\Reference\Page
{
	use Concerns\HasMessage;

	protected $layout;

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
			'LAYOUT' => $this->getLayout(),
			'FORM_ID' => 'YANDEX_PAY_ADMIN_TRADING_ADD',
			'PROVIDER_CLASS_NAME' => Pay\Component\Model\Form::class,
			'DATA_CLASS_NAME' => Pay\Trading\Setup\RepositoryTable::class,
			'TITLE' => self::getMessage('TITLE'),
			'TITLE_ADD' => self::getMessage('TITLE_ADD'),
			'BTN_SAVE' => $isNew ? self::getMessage('BTN_ADD') : self::getMessage('BTN_SAVE'),
			'ALLOW_SAVE' => Pay\Ui\Access::hasRights($this->getWriteRights()),
			'LIST_URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_grid', $baseQuery),
			'SAVE_URL' => $isNew ? Pay\Ui\Admin\Path::getModuleUrl('trading_setup', $baseQuery) . '&id=#ID#' : null,
			'NEXT_URL' => $isNew ? Pay\Ui\Admin\Path::getModuleUrl('trading_setup', $baseQuery) . '&view=dialog&id=#ID#' : null,
			'NEXT_PARAMETERS' => [
				'width' => 800,
				'height' => 600,
				'title' => self::getMessage('NEXT_TITLE'),
			],
			'PRIMARY' => $primary,
			'COPY' => $useCopy,
			'CONTEXT_MENU' => [
				[
					'ICON' => 'btn_list',
					'LINK' => Pay\Ui\Admin\Path::getModuleUrl('trading_grid', $baseQuery),
					'TEXT' => self::getMessage('CONTEXT_MENU_LIST'),
				],
			],
			'TABS' => [
				[
					'name' => self::getMessage('TAB_COMMON'),
					'fields' => [
						'SITE_ID',
						'PERSON_TYPE_ID',
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