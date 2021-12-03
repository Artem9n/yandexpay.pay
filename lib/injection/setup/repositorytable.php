<?php

namespace YandexPay\Pay\Injection\Setup;

use Bitrix\Main\ORM;
use YandexPay\Pay;
use YandexPay\Pay\Ui\Userfield;

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
        return 'yapay_injection';
    }

    public static function getMap() : array
    {
        return [
            new ORM\Fields\IntegerField('ID', [
                'autocomplete' => true,
                'primary' => true,
            ]),

            new ORM\Fields\IntegerField('TRADING_ID', [
                'required' => true,
            ]),

            new ORM\Fields\StringField('BEHAVIOR', [
                'required' => true,
            ]),

	        new ORM\Fields\StringField('INSERT_POSITION', [
		        'required' => true,
	        ]),

	        new ORM\Fields\BooleanField('ACTIVE', Userfield\BooleanType::getTableFieldDescription(false) + [
				'required' => true,
	        ]),

	        (new ORM\Fields\ArrayField('SETTINGS'))
	            ->configureSerializationPhp(),

            new ORM\Fields\Relations\Reference(
				'TRADING',
                Pay\Trading\Setup\RepositoryTable::class,
                ORM\Query\Join::on('this.TRADING_ID', 'ref.ID')
            ),
        ];
    }


}