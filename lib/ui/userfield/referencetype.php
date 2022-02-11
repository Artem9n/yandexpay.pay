<?php
namespace YandexPay\Pay\Ui\UserField;

use YandexPay\Pay\Injection;
use YandexPay\Pay\Exceptions\Facade;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Reference\Storage;

class ReferenceType extends FieldsetType
{
	use Concerns\HasMessage;

	public static function onBeforeSave($userField, $value)
	{
		if (isset($userField['MULTIPLE']) && $userField['MULTIPLE'] !== 'N')
		{
			if (!is_array($value)) { $value = []; }

			$result = [];

			// add/update

			foreach ($value as $one)
			{
				$saved = static::saveReference($userField, $one);

				if ($saved === null) { continue; }

				$result[] = $saved;
			}

			// delete

			$previous = Helper\ComplexValue::asMultiple($userField, null);
			$previousMap = static::mapValues($userField, $previous);
			$storedMap = static::mapValues($userField, $result);
			$toDelete = array_diff_key($previousMap, $storedMap);

			foreach ($toDelete as $one)
			{
				static::deleteReference($userField, $one);
			}
		}
		else
		{
			$previous = Helper\ComplexValue::asSingle($userField, null);
			$result = static::saveReference($userField, $value);
		}

		return $result;
	}

	protected static function convertValue(array $userField, $value)
	{
		if (!is_array($value)) { return null; }

		[$primary] = static::extractPrimary($userField, $value);

		if (empty($primary)) { return $value; }

		/** @var \Bitrix\Main\ORM\Data\DataManager $dataClass */
		$dataClass = static::referenceDataClass($userField);
		$query = $dataClass::getByPrimary($primary);
		$row = $query->fetch();

		if (!$row) { return null; }

		return $row;
	}

	protected static function getFields(array $userField) : array
	{
		/** @var Storage\View $view */
		$dataClass = static::referenceDataClass($userField);
		$referenceMap = static::referenceMap($userField);
		$fields = $dataClass::getView()->getFields();
		$fields = array_map(static function(array $field) {
			if ($field['EDIT_IN_LIST'] === 'N')
			{
				$field['HIDDEN'] = 'Y';
			}

			return $field;
		}, $fields);

		return array_diff_key($fields, array_flip($referenceMap));
	}

	/**
	 * @param array $userField
	 *
	 * @return class-string<\Bitrix\Main\ORM\Data\DataManager&Storage\HasView>
	 */
	protected static function referenceDataClass(array $userField) : string
	{
		$className = $userField['SETTINGS']['DATA_CLASS'] ?? null;

		Assert::notNull($className, '$userField[SETTINGS][DATA_CLASS]');
		Assert::isSubclassOf($className, Storage\HasView::class);

		return $className;
	}

	protected static function referenceMap(array $userField) : array
	{
		$map = $userField['SETTINGS']['REFERENCE'] ?? null;

		Assert::notNull($map, 'userField[SETTINGS][REFERENCE]');
		Assert::isArray($map, 'userField[SETTINGS][REFERENCE]');

		return $map;
	}

	public static function referenceMake(array $userField) : array
	{
		$map = static::referenceMap($userField);
		$row = [
			'ID' => $userField['ENTITY_VALUE_ID'],
		];
		$result = [];

		foreach ($map as $from => $to)
		{
			$result[$to] = $row[$from];
		}

		return $result;
	}

	protected static function saveReference(array $userField, $value) : ?array
	{
		if (empty($value) || !is_array($value)) { return null; }

		/** @var \Bitrix\Main\ORM\Data\DataManager $dataClass*/
		$dataClass = static::referenceDataClass($userField);
		[$primary, $data] = static::extractPrimary($userField, $value);

		$prefix = mb_strtoupper($data['BEHAVIOR']);
		$selectorKey = $prefix . '_SELECTOR';
		$data['SETTINGS'][$selectorKey] = htmlspecialcharsbx($data['SETTINGS'][$selectorKey]);

		if (!empty($primary))
		{
			$saveResult = $dataClass::update($primary, $data);
		}
		else
		{
			$link = static::referenceMake($userField);

			$saveResult = $dataClass::add($data + $link);
			$primary = $saveResult->getPrimary();
		}

		Facade::handleResult($saveResult);

		return $primary;
	}

	protected static function deleteReference(array $userField, array $value) : void
	{
		/** @var \Bitrix\Main\ORM\Data\DataManager $dataClass*/
		$dataClass = static::referenceDataClass($userField);
		[$primary] = static::extractPrimary($userField, $value);

		if (empty($primary)) { return; }

		$dataClass::delete($primary);
	}

	protected static function mapValues(array $userField, array $values) : array
	{
		$result = [];

		foreach ($values as $value)
		{
			$primary = static::stringifyPrimary($userField, $value);

			if ($primary === null) { continue; }

			$result[$primary] = $value;
		}

		return $result;
	}

	protected static function stringifyPrimary(array $userField, array $value) : ?string
	{
		[$primary] = static::extractPrimary($userField, $value);

		if (empty($primary)) { return null; }

		return implode(':', $primary);
	}

	protected static function extractPrimary(array $userField, array $value) : array
	{
		/** @var \Bitrix\Main\ORM\Data\DataManager $dataClass*/
		$dataClass = static::referenceDataClass($userField);
		$primaryFields = $dataClass::getEntity()->getPrimaryArray();
		$primary = array_intersect_key($value, array_flip($primaryFields));
		$data = array_diff_key($value, $primary);
		$primary = array_filter($primary);

		return [$primary, $data];
	}

	public static function checkFields(array $userFields, $value) : array
	{
		$result = [];

		if (empty($value['SETTINGS'])) { return $result; }

		$type = $value['BEHAVIOR'];
		$instance = Injection\Behavior\Registry::getInstance($type);
		$fields = $instance->getFields();

		$prefix = mb_strtoupper($type) . '_';
		$prefixLength = mb_strlen($prefix);

		foreach ($value['SETTINGS'] as $code => $val)
		{
			if (mb_strpos($code, $prefix) !== 0) { continue; }

			$optionName = mb_substr($code, $prefixLength);

			if (
				isset($fields[$optionName])
				&& $fields[$optionName]['MANDATORY'] === 'Y'
				&& empty(trim($val))
			)
			{
				$result[] = [
					'text' => static::getMessage('INJECTION_FIELD', [
						'#FIELD#' => $fields[$optionName]['TITLE'],
						'#BEHAVIOR#' => $instance->getTitle()
					])
				];
			}
		}

		return $result;
	}
}