<?php

namespace YandexPay\Pay\Trading\Injection;

use Bitrix\Main\ORM;
use YandexPay\Pay;

class RepositoryTable extends ORM\Data\DataManager
    implements Pay\Reference\Storage\HasView

{
    public static function getView() : Pay\Reference\Storage\View
    {
        return new View(static::class);
    }
    public static function getCollectionClass() : string
    {
        return Collection::class;
    }

    public static function getObjectClass() : string
    {
        return Model::class;
    }

    public static function getTableName() : string
    {
        return 'yapay_trading_injection';
    }

    public static function getMap() : array
    {
        return [
            new ORM\Fields\IntegerField('ID', [
                'autocomplete' => true,
                'primary' => true,
            ]),
            new ORM\Fields\IntegerField('SETUP_ID', [
                'required' => true,
            ]),
            new ORM\Fields\StringField('BEHAVIOR', [
                'required' => true,
                'primary' => true,
            ]),
            new ORM\Fields\StringField('SELECTOR', [
                'required' => true,
                'primary' => true,
            ]),
            new ORM\Fields\Relations\Reference('SETUP',
                Pay\Trading\Setup\RepositoryTable::class,
                ORM\Query\Join::on('this.SETUP_ID', 'ref.ID')),

        ];
    }


}