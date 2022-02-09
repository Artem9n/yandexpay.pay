<?php

namespace YandexPay\Pay\Component\Plain;

use YandexPay\Pay;
use Bitrix\Main;

abstract class Form extends Pay\Component\Reference\Form
{
	use Pay\Reference\Concerns\HasMessage;

	protected $fields;

	public function prepareComponentParams(array $params) : array
	{
		Pay\Reference\Assert::notNull($params['TABS'], 'params[TABS]');
		Pay\Reference\Assert::notNull($params['FIELDS'], 'params[FIELDS]');

		$params['TABS'] = $this->prepareTabs($params['TABS'], $params['FIELDS']);

		return $params;
	}

	protected function prepareTabs(array $tabs, array $fields) : array
	{
		$tabs = $this->extendTabs($tabs, $fields);

		return $this->sortTabs($tabs);
	}

	protected function extendTabs(array $tabs, array $fields) : array
	{
		$result = [];
		$usedFields = [];

		foreach ($tabs as $tabKey => $tab)
		{
			// fields

			if (!isset($tab['fields']))
			{
				$tabCode = !is_numeric($tabKey) ? $tabKey : 'COMMON';
				$tabFields = $this->getFieldCodesForTab($fields, $tabCode);

				$tab['fields'] = array_diff($tabFields, $usedFields);
			}

			/** @noinspection SlowArrayOperationsInLoopInspection */
			$usedFields = array_merge($usedFields, $tab['fields']);

			// export

			$result[] = $tab;
		}

		return $result;
	}

	protected function sortTabs(array $tabs) : array
	{
		$tabsWithSort = array_filter($tabs, static function($tab) { return isset($tab['sort']); });

		if (count($tabsWithSort) > 0)
		{
			uasort($tabs, static function(array $tabA, array $tabB) {
				$sortA = $tabA['sort'] ?? 5000;
				$sortB = $tabB['sort'] ?? 5000;

				if ($sortA === $sortB) { return 0; }

				return $sortA < $sortB ? -1 : 1;
			});
		}

		return $tabs;
	}

	protected function getFieldCodesForTab(array $fields, string $tabCode) : array
	{
		$result = [];

		foreach ($fields as $fieldCode => $field)
		{
			$fieldTab = $field['TAB'] ?? 'COMMON';

			if ($fieldTab === $tabCode)
			{
				$result[] = $fieldCode;
			}
		}

		return $result;
	}

	public function modifyRequest(array $request, array $fields) : array
	{
		return $this->sanitizeUserFields($request, $fields);
	}

	protected function sanitizeUserFields(array $request, array $fields) : array
	{
		foreach ($fields as $fieldName => $userField)
		{
			if (!array_key_exists($fieldName, $request)) { continue; }

			if (!empty($userField['SETTINGS']['READONLY']))
			{
				unset($request[$fieldName]);
				continue;
			}

			$requestValue = $request[$fieldName];

			if ($userField['MULTIPLE'] === 'Y')
			{
				$sanitizedValues = [];
				$requestValue = is_array($requestValue) ? $requestValue : [];

				foreach ($requestValue as $requestValueItem)
				{
					$sanitizedValue = $this->sanitizeUserFieldValue($userField, $requestValueItem);

					if (!Pay\Utils\Value::isEmpty($sanitizedValue))
					{
						$sanitizedValues[] = $sanitizedValue;
					}
				}

				if (!empty($sanitizedValues))
				{
					$request[$fieldName] = $sanitizedValues;
				}
				else
				{
					$request[$fieldName] = [];
				}
			}
			else
			{
				$request[$fieldName] = $this->sanitizeUserFieldValue($userField, $requestValue);
			}
		}

		return $request;
	}

	protected function sanitizeUserFieldValue(array $userField, $value)
	{
		$result = $value;

		if (
			!empty($userField['USER_TYPE']['CLASS_NAME'])
			&& is_callable([$userField['USER_TYPE']['CLASS_NAME'], 'SanitizeFields'])
		)
		{
			$result = call_user_func(
				[$userField['USER_TYPE']['CLASS_NAME'], 'SanitizeFields'],
				$userField,
				$value
			);
		}

		return $result;
	}

	public function extend(array $data, array $select = []) : array
	{
		return $this->restoreDefaultsForHiddenFields($data, $select);
	}

	protected function restoreDefaultsForHiddenFields(array $data, array $select) : array
	{
		$fields = $this->getAllFields();
		$result = $data;

		if (empty($select))
		{
			$select = array_keys($fields);
		}

		foreach ($select as $fieldName)
		{
			if (!isset($fields[$fieldName])) { continue; }

			$field = $fields[$fieldName];

			if (!empty($field['DEPEND_HIDDEN']) && isset($field['SETTINGS']['DEFAULT_VALUE']))
			{
				$fieldValue = array_key_exists($fieldName, $data) ? $data[$fieldName] : $field['VALUE'];

				if ($fieldValue === false)
				{
					$result[$fieldName] = $field['SETTINGS']['DEFAULT_VALUE'];
				}
			}
		}

		return $result;
	}

	public function validate(array $data, array $fields = null) : Main\Result
	{
		if ($fields === null) { return new Main\Entity\Result(); }

		return $this->validateUserFields($data, $fields);
	}

	protected function validateUserFields(array $data, array $fields) : Main\Entity\Result
	{
		$result = new Main\Entity\Result();

		foreach ($fields as $fieldName => $userField)
		{
			if (!empty($userField['SETTINGS']['READONLY']) || !empty($userField['DEPEND_HIDDEN'])) { continue; }
			if (!empty($userField['HIDDEN']) && $userField['HIDDEN'] !== 'N') { continue; }

			$dataField = $data[$fieldName] ?? null;

			if ($userField['MULTIPLE'] === 'Y')
			{
				$values = is_array($dataField) ? $dataField : [];
			}
			else
			{
				$values = !Pay\Utils\Value::isEmpty($dataField) ? [ $dataField ] : [];
			}

			if (!empty($values))
			{
				foreach ($values as $value)
				{
					$checkResult = $this->checkUserFieldValue($userField, $value);

					if (!$checkResult->isSuccess())
					{
						$result->addErrors($checkResult->getErrors());
					}
				}
			}
			else if ($userField['MANDATORY'] === 'Y')
			{
				$message = self::getMessage('FIELD_REQUIRED', [
					'#FIELD_NAME#' => $userField['EDIT_FORM_LABEL'] ?: $fieldName
				]);
				$error = new Main\ORM\EntityError($message);

				$result->addError($error);
			}
		}

		return $result;
	}

	protected function checkUserFieldValue(array $userField, $value) : Main\Entity\Result
	{
		$result = new Main\Entity\Result();

		if (!empty($userField['USER_TYPE']['CLASS_NAME']) && is_callable([$userField['USER_TYPE']['CLASS_NAME'], 'CheckFields']))
		{
			$userErrors = call_user_func(
				[$userField['USER_TYPE']['CLASS_NAME'], 'CheckFields'],
				$userField,
				$value
			);

			if (!empty($userErrors) && is_array($userErrors))
			{
				foreach ($userErrors as $userError)
				{
					$error = new Main\ORM\EntityError($userError['text'], 0);
					$result->addError($error);
				}
			}
		}

		return $result;
	}

	protected function sliceFieldsDependHidden(array $fields, array $values) : array
	{
		$result = $values;

		foreach ($fields as $fieldName => $field)
		{
			if (empty($field['DEPEND_HIDDEN']) || $field['FIELD_NAME'] === 'INJECTION') { continue; }

			Pay\Utils\BracketChain::unset($result, $fieldName);
		}

		return $result;
	}

	protected function applyUserFieldsOnBeforeSave(array $fields, array $values) : array
	{
		$result = $values;

		foreach ($fields as $fieldName => $field)
		{
			if (
				isset($field['USER_TYPE']['CLASS_NAME'])
				&& is_callable([$field['USER_TYPE']['CLASS_NAME'], 'onBeforeSave'])
			)
			{
				$userField = $field;
				$userField['ENTITY_VALUE_ID'] = $this->getComponentParam('PRIMARY') ?: null;
				$userField['VALUE'] = $this->component->getOriginalValue($field);

				$fieldValue = Pay\Utils\BracketChain::get($values, $fieldName);
				$fieldValue = call_user_func(
					[$field['USER_TYPE']['CLASS_NAME'], 'onBeforeSave'],
					$userField,
					$fieldValue
				);

				Pay\Utils\BracketChain::set($result, $fieldName, $fieldValue);
			}
		}

		return $result;
	}

	public function getFields(array $select = [], array $item = null) : array
	{
		$allFields = $this->getAllFields();

		if (empty($select))
		{
			$result = $allFields;
		}
		else
		{
			$selectMap = array_flip($select);
			$result = array_intersect_key($allFields, $selectMap);
		}

		return $result;
	}

	protected function getAllFields() : array
	{
		return (array)$this->getComponentParam('FIELDS');
	}

	public function getRequiredParams() : array
	{
		return [
			'FIELDS',
		];
	}
}