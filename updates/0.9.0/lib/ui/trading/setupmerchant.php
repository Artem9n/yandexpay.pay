<?php
namespace YandexPay\Pay\Ui\Trading;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay;

class SetupMerchant extends Pay\Ui\Reference\Page
{
	use Concerns\HasMessage;

	protected $layout;

	public function show() : void
	{
		global $APPLICATION;

		Main\UI\Extension::load('yandexpaypay.admin.ui');

		$APPLICATION->IncludeComponent('yandexpay.pay:admin.form', '', [
			'LAYOUT' => $this->getLayout(),
			'FORM_ID' => 'YANDEX_PAY_ADMIN_TRADING_MERCHANT',
			'PROVIDER_CLASS_NAME' => Pay\Component\Trading\Merchant\Form::class,
			'TITLE' => self::getMessage('TITLE'),
			'ALLOW_SAVE' => Pay\Ui\Access::hasRights($this->getWriteRights()),
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