<?php
namespace YandexPay\Pay\Ui\UserField;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Settings\Options;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;

/** @noinspection PhpUnused */
class WarehouseType extends FieldsetType
{
	use Concerns\HasMessage;

	public const USER_TYPE_ID = 'yapay_warehouse';

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
	public static function onBeforeSave(array $userField, $value) : string
	{
		if (is_array($value))
		{
			$value = serialize($value);
		}

		return $value;
	}

	protected static function asSingle(array $userField, ?array $htmlControl)
	{
		if (!is_array($userField['VALUE']))
		{
			$userField['VALUE'] = unserialize($userField['VALUE'], [ 'allowed_classes' => false ]);
		}

		return parent::asSingle($userField, $htmlControl);
	}

	protected static function makeLayout($userField, $htmlControl) : Fieldset\AbstractLayout
	{
		$userField = static::userFieldDefaults($userField);
		$fields = static::getFields($userField);

		$result = new Fieldset\SummaryLayout($userField, $htmlControl['NAME'], $fields);
		$result->formRenderer(static function(array $fields, array $values, array $settings = []) {
			global $APPLICATION;

			ob_start();

			$APPLICATION->IncludeComponent('yandexpay.pay:admin.field.warehouse', '', [
				'FIELDS' => $fields,
				'VALUES' => $values,
				'SETTINGS' => $settings,
			]);

			return ob_get_clean();
		});

		return $result;
	}

	protected static function userFieldDefaults(array $userField) : array
	{
		$userField['NAME'] = static::getMessage('NAME');

		if (!isset($userField['SETTINGS'])) { $userField['SETTINGS'] = []; }

		$userField['SETTINGS'] += [
			'SUMMARY' => '#COUNTRY#, #LOCALITY#, #STREET#, #BUILDING#',
			'LAYOUT' => 'summary',
			'MODAL_WIDTH' => 800,
			'MODAL_HEIGHT' => 600,
		];

		return $userField;
	}

	public static function getFields(array $userField) : array
	{
		if (isset($userField['FIELDS'])) { return $userField['FIELDS']; }

		$option = new Options\Warehouse();
		$environment = EntityRegistry::getEnvironment();
		$siteId = $environment->getSite()->getDefault();

		return $option->getFields($environment, $siteId);
	}
}