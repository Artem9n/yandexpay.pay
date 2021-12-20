<?php

namespace YandexPay\Pay\Ui\UserField\Fieldset;

use Bitrix\Main;
use YandexPay\Pay\Ui\UserField;

abstract class AbstractLayout
{
	public const NAME_BASE = 'js-fieldset';

	protected $userField;
	protected $name;
	protected $fields;
	protected $fieldsetName;

	public function __construct($userField, $name, array $fields)
	{
		$this->userField = $userField;
		$this->name = $name;
		$this->fields = $fields;
		$this->fieldsetName = $this->hasParentFieldset()
			? static::NAME_BASE . '-' . Main\Security\Random::getString(3)
			: static::NAME_BASE;
	}

	abstract public function edit($value) : string;

	abstract public function editMultiple($values) : string;

	protected function extendFields(string $name, array $fields) : array
	{
		foreach ($fields as $fieldKey => &$field)
		{
			$fieldName = $name;
			$fieldName .= $this->isComplexFieldName($fieldKey)
				? $this->makeChildFieldName($fieldKey)
				: sprintf('[%s]', $fieldKey);

			$field = UserField\Helper\Field::extend($field, $fieldName);

			if (!isset($field['SETTINGS'])) { $field['SETTINGS'] = []; }

			$field['SETTINGS']['PARENT_FIELDSET_NAME'] = $this->name;
			$field['SETTINGS']['PARENT_FIELDSET_BASE'] = $this->fieldsetName;
		}
		unset($field);

		return $fields;
	}

	protected function isComplexFieldName(string $fieldName) : bool
	{
		return mb_strpos($fieldName, '[') !== false;
	}

	protected function makeChildFieldName(string $fieldName) : string
	{
		$bracketPosition = mb_strpos($fieldName, '[');

		if ($bracketPosition === false || $bracketPosition === 0)
		{
			$result = $fieldName;
		}
		else
		{
			$basePart = mb_substr($fieldName, 0, $bracketPosition);
			$leftPart = mb_substr($fieldName, $bracketPosition);

			$result = '[' . $basePart . ']';
			$result .= $leftPart;
		}

		return $result;
	}

	protected function hasParentFieldset() : bool
	{
		return Helper::hasParentFieldset($this->userField);
	}

	protected function getParentFieldsetName(string $type) : string
	{
		return Helper::getParentFieldsetName($this->userField, $type);
	}

	protected function getFieldsetName(string $type) : string
	{
		return $this->fieldsetName . '-' . $type;
	}

	protected function getPluginAttributes(string $inputName) : array
	{
		if ($this->hasParentFieldset())
		{
			$selfName = Helper::makeRelativeName($this->userField, $inputName);

			$result = [
				'class' => $this->getParentFieldsetName('row__child'),
				'data-name' => $selfName,
				'data-element-namespace' => '.' . $this->fieldsetName,
			];
		}
		else
		{
			$result = [
				'class' => 'js-plugin',
				'data-base-name' => $inputName,
			];
		}

		return $result;
	}

	protected function resolveRowValues($values) : array
	{
		if (!is_array($values))
		{
			$values = [];
		}

		if (isset($this->userField['ROW']))
		{
			$values['PARENT_ROW'] = $this->userField['ROW'];
		}

		return $values;
	}

	protected function prepareFieldControl(string $control, string $fieldKey, array $field) : string
	{
		$attributes = [
			'class' => $this->getFieldsetName('row__input'),
		];
		$dataName = $this->isComplexFieldName($fieldKey)
			? $this->makeChildFieldName($fieldKey)
			: $fieldKey;

		$control = UserField\Helper\Attributes::insert($control, $attributes, static function($tagName, $existsAttributes) {
			return (
				!isset($existsAttributes['class'])
				|| mb_strpos($existsAttributes['class'], AbstractLayout::NAME_BASE) === false
			);
		});

		return UserField\Helper\Attributes::insertDataName($control, $dataName, $field['FIELD_NAME']);
	}
}