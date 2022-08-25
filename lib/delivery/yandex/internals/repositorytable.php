<?php

namespace YandexPay\Pay\Delivery\Yandex\Internals;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Sale\Delivery;

class RepositoryTable extends ORM\Data\DataManager
{
	public static function getObjectClass() : string
	{
		return Model::class;
	}

	public static function getTableName() : string
	{
		return 'yapay_yandex_delivery_transport_request';
	}

	public static function getMap() : array
	{
		return [
			new ORM\Fields\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true,
			]),
			'TIMESTAMP_X' => new ORM\Fields\DatetimeField('TIMESTAMP_X', [
				'default_value' => function() {
					return new Main\Type\DateTime();
				}
			]),
			new ORM\Fields\IntegerField('REQUEST_ID', [
				'required' => true,
			]),
			new ORM\Fields\Relations\Reference(
				'REQUEST',
				Delivery\Requests\RequestTable::class,
				ORM\Query\Join::on('this.REQUEST_ID', 'ref.ID')
			),
			new ORM\Fields\StringField('STATUS', [
				'required' => true,
			]),
			new ORM\Fields\BooleanField('CONFIRM', [
				'values' => ['N', 'Y'],
				'default_value' => 'Y',
			]),
			(new ORM\Fields\ArrayField('PAYLOAD'))
				->configureSerializationPhp(),
		];
	}
}