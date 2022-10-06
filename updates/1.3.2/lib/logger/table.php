<?php
namespace YandexPay\Pay\Logger;

use Bitrix\Main\ORM;
use YandexPay\Pay;
use YandexPay\Pay\Reference\Storage;

class Table extends ORM\Data\DataManager implements Pay\Reference\Storage\HasView
{
	public static function getTableIndexes() : array
	{
		return [
			0 => ['TIMESTAMP_X'],
		];
	}

	public static function getView() : Storage\View
	{
		return new View(static::class);
	}

	public static function getTableName() : string
	{
		return 'yapay_log';
	}

	public static function getMap() : array
	{
		return [
			new ORM\Fields\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			new ORM\Fields\DatetimeField('TIMESTAMP_X', [
				'required' => true,
			]),
			new ORM\Fields\IntegerField('SETUP_ID'),
			new ORM\Fields\EnumField('LEVEL', [
				'required' => true,
				'values' => [
					Pay\Psr\Log\LogLevel::EMERGENCY => 'emergency',
					Pay\Psr\Log\LogLevel::ALERT => 'alert',
					Pay\Psr\Log\LogLevel::CRITICAL => 'critical',
					Pay\Psr\Log\LogLevel::ERROR => 'error',
					Pay\Psr\Log\LogLevel::WARNING => 'warning',
					Pay\Psr\Log\LogLevel::NOTICE => 'notice',
					Pay\Psr\Log\LogLevel::INFO => 'info',
					Pay\Psr\Log\LogLevel::DEBUG => 'debug',
				],
			]),
			new ORM\Fields\EnumField('AUDIT', [
				'required' => true,
				'values' => [
					Audit::INCOMING_REQUEST => Audit::INCOMING_REQUEST,
					Audit::INCOMING_RESPONSE => Audit::INCOMING_RESPONSE,
					Audit::OUTGOING_REQUEST => Audit::OUTGOING_REQUEST,
					Audit::OUTGOING_RESPONSE => Audit::OUTGOING_RESPONSE,
					Audit::INJECTION_ELEMENT => Audit::INJECTION_ELEMENT,
					Audit::YANDEX_DELIVERY => Audit::YANDEX_DELIVERY,
					Audit::DELIVERY_COLLECTOR => Audit::DELIVERY_COLLECTOR,
					Audit::UNKNOWN => Audit::UNKNOWN,
				],
			]),
			new Storage\Field\LongTextField('MESSAGE', []),
			new ORM\Fields\StringField('URL', []),
		];
	}
}