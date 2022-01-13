<?php

namespace YandexPay\Pay\Trading\Setup;

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
		return 'yapay_trading_setup';
	}

	public static function getMap() : array
	{
		return [
			new ORM\Fields\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			new ORM\Fields\StringField('SITE_ID', [
				'required' => true,
				'validation' => [static::class, 'validateSiteId'],
			]),
			new ORM\Fields\StringField('PERSON_TYPE_ID', [
				'required' => true,
				'validation' => [static::class, 'validatePersonTypeId'],
			]),
			new ORM\Fields\BooleanField('ACTIVE', Pay\Ui\Userfield\BooleanType::getTableFieldDescription(true) + [
				'required' => true,
			]),
            new Pay\Reference\Storage\Field\OneToMany('SETTINGS', Pay\Trading\Settings\RepositoryTable::class, 'SETUP'),

            new ORM\Fields\Relations\OneToMany(
                'INJECTION',
                Pay\Injection\Setup\RepositoryTable::class,
                'TRADING'//ORM\Query\Join::on('this.ID', 'ref.TRADING_ID')
            ),
		];
	}

	public static function validateSiteId() : array
	{
		return [
			new ORM\Fields\Validators\LengthValidator(null, 2),
		];
	}

	public static function validatePersonTypeId() : array
	{
		return [
			new ORM\Fields\Validators\LengthValidator(null, 3),
		];
	}
}