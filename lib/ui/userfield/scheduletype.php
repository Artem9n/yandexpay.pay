<?php

namespace YandexPay\Pay\Ui\UserField;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Settings\Options;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;

/** @noinspection PhpUnused */
class ScheduleType extends FieldsetType
{
	use Concerns\HasMessage;

	public const USER_TYPE_ID = 'yapay_schedule';

	public function getUserTypeDescription() : array
	{
		return [
			'DESCRIPTION' => static::getMessage('NAME'),
			'USER_TYPE_ID' => static::USER_TYPE_ID,
			'CLASS_NAME' => static::class,
			'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
		];
	}

	/** @noinspection PhpUnusedParameterInspection */
	public function getDBColumnType(array $userField) : string
	{
		return 'text';
	}

	/** @noinspection PhpUnusedParameterInspection */
	public static function onBeforeSave(array $userField, $value)
	{
		if (is_array($value))
		{
			$value = serialize($value);
		}

		return $value;
	}

	protected static function asMultiple(array $userField, ?array $htmlControl) : array
	{
		if (is_array($userField['VALUE']))
		{
			foreach ($userField['VALUE'] as &$value)
			{
				if (is_array($value)) { continue; }

				$value = unserialize($value, [ 'allowed_classes' => false ]);
			}
			unset($value);
		}

		return parent::asMultiple($userField, $htmlControl);
	}

	protected static function asSingle(array $userField, ?array $htmlControl)
	{
		if (!is_array($userField['VALUE']))
		{
			$userField['VALUE'] = unserialize($userField['VALUE'], [ 'allowed_classes' => false ]);
		}

		return parent::asSingle($userField, $htmlControl);
	}

	public static function getFields(array $userField) : array
	{
		if (isset($userField['FIELDS'])) { return $userField['FIELDS']; }

		$option = new Options\ScheduleOption();
		$environment = EntityRegistry::getEnvironment();
		$siteId = $environment->getSite()->getDefault();

		return $option->getFields($environment, $siteId);
	}
}