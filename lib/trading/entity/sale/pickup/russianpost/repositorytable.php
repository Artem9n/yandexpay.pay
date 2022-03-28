<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup\RussianPost;

use Bitrix\Main\ORM;

class RepositoryTable extends ORM\Data\DataManager
{
	public static function getTableName() : string
	{
		return 'yapay_pickup_russian_post';
	}

	public static function getMap() : array
	{
		return array(
			new ORM\Fields\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			new ORM\Fields\StringField('ADDRESS'),
			new ORM\Fields\StringField('HOUSE'),
			new ORM\Fields\StringField('INDEX'),
			new ORM\Fields\StringField('PLACE'),
			new ORM\Fields\StringField('REGION'),
			new ORM\Fields\StringField('STREET'),
			new ORM\Fields\StringField('LATITUDE'),
			new ORM\Fields\StringField('LONGITUDE'),
			new ORM\Fields\StringField('WORK_TIME'),
			new ORM\Fields\StringField('HASH'),
		);
	}
}