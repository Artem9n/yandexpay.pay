<?php
namespace YandexPay\Pay\Ui\UseCase;

use Bitrix\Main;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Reference;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Ui\UserField;
use YandexPay\Pay\Utils;

class AutoInstallInjection extends Reference\Event\Regular
{
	public static function getHandlers() : array
	{
		return [
			[
				'module' => 'sale',
				'event' => '\\Bitrix\\Sale\\Internals\\PaySystemAction::' . Main\ORM\Data\DataManager::EVENT_ON_AFTER_ADD,
				'method' => 'onAfterAdd',
			],
		];
	}

	public static function onAfterAdd(Main\Event $event) : void
	{
		try
		{
			$id = $event->getParameter('id');
			$data = $event->getParameter('fields');

			if ($data['ACTION_FILE'] !== 'yandexpay') { return; }

			if (static::isExists()) { return; }

			$setup = static::createTrading();
			$values = static::collectSettings($setup, $id);

			static::saveSettings($setup, $values);
		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}
	}

	protected static function isExists() : bool
	{
		$query = Trading\Setup\RepositoryTable::getList([
			'limit' => 1,
			'select' => [ 'ID' ],
		]);

		return (bool)$query->fetch();
	}

	protected static function createTrading() : Trading\Setup\Model
	{
		$trading = new Trading\Setup\Model();

		$siteId = $trading->getEnvironment()->getSite()->getDefault();
		$individualTypeId = $trading->getEnvironment()->getPersonType()->getIndividualId($siteId);
		$legalTypeId = $trading->getEnvironment()->getPersonType()->getLegalId($siteId);

		$personTypeId = $individualTypeId ?? $legalTypeId;

		$trading->setSiteId($siteId);
		$trading->setPersonTypeId($personTypeId);
		$trading->setActive(true);

		$saveResult = $trading->save();

		Exceptions\Facade::handleResult($saveResult);

		return $trading;
	}

	protected static function collectSettings(Trading\Setup\Model $setup, int $paySystemId) : array
	{
		$fields = static::getSettingsFields($setup);
		$values = [ 'PAYSYSTEM_CARD' => $paySystemId ];
		$values += static::collectDefaultSettings($fields);

		$values = static::applyFieldsBeforeSave($setup->getId(), $fields, $values);

		return $values;
	}

	protected static function getSettingsFields(Trading\Setup\Model $setup) : array
	{
		$options = $setup->getOptions();
		$fields = $options->getFields($setup->getEnvironment(), $setup->getSiteId());

		foreach ($fields as $name => &$field)
		{
			$field = UserField\Helper\Field::extend($field, $name);
		}
		unset($field);

		return $fields;
	}

	protected static function collectDefaultSettings(array $fields) : array
	{
		$values = [];

		foreach ($fields as $fieldName => $field)
		{
			if (isset($field['SETTINGS']['DEFAULT_VALUE']))
			{
				Utils\BracketChain::set($values, $fieldName, $field['SETTINGS']['DEFAULT_VALUE']);
			}
			else if (
				isset($field['USER_TYPE']['CLASS_NAME'])
				&& is_callable([$field['USER_TYPE']['CLASS_NAME'], 'GetList'])
			)
			{
				$options = call_user_func([$field['USER_TYPE']['CLASS_NAME'], 'GetList'], $field);

				if (!($options instanceof \CDBResult)) { continue; }

				while ($option = $options->Fetch())
				{
					if (isset($option['DEF']) && $option['DEF'] === 'Y')
					{
						Utils\BracketChain::set($values, $fieldName, $option['ID']);
						break;
					}
				}
			}
		}

		return $values;
	}

	protected static function applyFieldsBeforeSave(int $primary, array $fields, array $values) : array
	{
		$result = $values;

		foreach ($fields as $fieldName => $field)
		{
			if (!array_key_exists($fieldName, $values)) { continue; }

			$field = UserField\Helper\Field::extend($field, $fieldName);

			if (
				isset($field['USER_TYPE']['CLASS_NAME'])
				&& is_callable([$field['USER_TYPE']['CLASS_NAME'], 'onBeforeSave'])
			)
			{
				$userField = $field;
				$userField['ENTITY_VALUE_ID'] = $primary;
				$userField['VALUE'] = null;

				$fieldValue = Utils\BracketChain::get($values, $fieldName);
				$fieldValue = call_user_func(
					[$field['USER_TYPE']['CLASS_NAME'], 'onBeforeSave'],
					$userField,
					$fieldValue
				);

				Utils\BracketChain::set($result, $fieldName, $fieldValue);
			}
		}

		return $result;
	}

	protected static function saveSettings(Trading\Setup\Model $setup, array $values) : void
	{
		$setup->syncSettings($values);
		$setup->getSettings()->save(true);
	}
}