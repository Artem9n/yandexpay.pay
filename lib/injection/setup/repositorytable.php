<?php

namespace YandexPay\Pay\Injection\Setup;

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

	        (new ORM\Fields\ArrayField('SETTINGS'))
	            ->configureSerializationPhp(),

            new ORM\Fields\Relations\Reference(
				'TRADING',
                Pay\Trading\Setup\RepositoryTable::class,
                ORM\Query\Join::on('this.TRADING_ID', 'ref.ID')
            ),
        ];
    }

	public static function OnBeforeUpdate(ORM\Event $event) : void
	{
		/** @var Model $model */
		$primary = $event->getParameter('id');
		$model = Model::wakeUp($primary);
		$model->unregister();
	}

	public static function OnAfterAdd(ORM\Event $event) : void
	{
		/** @var Model $model */
		$model = $event->getParameter('object');
		$model->register();
	}

	public static function OnAfterUpdate(ORM\Event $event) : void
	{
		/** @var Model $model */
		$model = $event->getParameter('object');
		$model->register();
	}

	public static function OnBeforeDelete(ORM\Event $event) : void
	{
		/** @var Model $model */
		$primary = $event->getParameter('id');
		$model = Model::wakeUp($primary);
		$model->unregister();
	}
}