<?php

namespace YandexPay\Pay\Trading\Settings;

use Bitrix\Main\ORM;
use YandexPay\Pay;

class RepositoryTable extends ORM\Data\DataManager
{
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
		return 'yapay_trading_settings';
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
				//'primary' => true,
			]),
			new ORM\Fields\StringField('NAME', [
				'required' => true,
				'primary' => true,
				'validation' => [static::class, 'validateName'],
			]),
			(new Pay\Reference\Storage\Field\FuzzyField('VALUE'))
				->configureRequired(true)
				->configureSerializationPhp(),
			new ORM\Fields\Relations\Reference(
				'SETUP',
				Pay\Trading\Setup\RepositoryTable::class,
				ORM\Query\Join::on('this.SETUP_ID', 'ref.ID')
			),
		];
	}


	public static function onAfterUpdate(ORM\Event $event)
	{
		$fields = $event->getParameter('fields');

		if (array_key_exists('SETUP_ID', $fields) && empty($fields['SETUP_ID']))
		{
			$primary = $event->getParameter('primary');

			static::delete($primary);
		}
	}

	public static function validateName() : array
	{
		return [
			new ORM\Fields\Validators\LengthValidator(null, 30),
		];
	}
}