<?php
namespace YandexPay\Pay\Ui\Trading;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay;

class Injection extends Pay\Ui\Reference\Page
{
    use Concerns\HasMessage;

    public function show() : void
    {
        global $APPLICATION;

        Main\UI\Extension::load('yandexpaypay.admin.ui');

        $primary = $this->request->get('id') ?: null;
        $baseQuery = [
            'lang' => LANGUAGE_ID,
        ];

        $APPLICATION->IncludeComponent('yandexpay.pay:admin.form', '', [
            'FORM_ID' => 'YANDEX_PAY_ADMIN_TRADING_INJECTION_ADD',
            'PROVIDER_CLASS_NAME' => Pay\Component\Trading\Injection\Form::class,
            'DATA_CLASS_NAME' => Pay\Trading\Injection\RepositoryTable::class,
            'TITLE' => self::getMessage('TITLE'),
            'ALLOW_SAVE' => Pay\Ui\Access::hasRights($this->getWriteRights()),
            'LIST_URL' => Pay\Ui\Admin\Path::getModuleUrl('trading_injection_grid', $baseQuery),
            'PRIMARY' => $primary,
            'CONTEXT_MENU' => [
                [
                    'ICON' => 'btn_list',
                    'LINK' => Pay\Ui\Admin\Path::getModuleUrl('trading_injection_grid', $baseQuery),
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
}