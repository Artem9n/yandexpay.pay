<?php

namespace YandexPay\Pay\Trading\Injection;

use Bitrix\Main;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Utils;

class Options
{/*
    use Concerns\HasMessage;

    public function getTabs() : array
    {
        return [
            'COMMON' => [
                'name' => self::getMessage('TAB_COMMON'),
                'sort' => 1000,
            ],
        ];
    }*/



/*    protected function getHandlerFields(Entity\Reference\Environment $environment, string $siteId) : array
    {
        return [

        ];
    }

    protected function getPurchaseFields(Entity\Reference\Environment $environment, string $siteId) : array
    {
        return [
            'PURCHASE_URL' => [
                'TYPE' => 'string',
                'MANDATORY' => 'Y',
                'NAME' => self::getMessage('PURCHASE_URL'),
                'GROUP' => self::getMessage('COMMON'),
                'SORT' => 2000,
                'VALUE' => static::getPurchaseUrl(),
                'SETTINGS' => [
                    'READONLY' => true,
                ]
            ]
        ];
    }

    public static function getPurchaseUrl() : string
    {
        return Utils\Url::absolutizePath(BX_ROOT . '/tools/' . Config::getModuleName() . '/purchase.php');
    }*/

    protected function getInjectionProperties(Entity\Reference\Environment $environment, string $siteId) : array
    {

    }

}