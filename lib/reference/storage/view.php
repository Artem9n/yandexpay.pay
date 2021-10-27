<?php

namespace YandexPay\Pay\Reference\Storage;

use Bitrix\Main\ORM;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Entity;

abstract class View
{
	/** @var ORM\Data\DataManager */
	protected $dataClass;
	protected $environment;


	public function __construct(string $dataClass)
	{
		$this->dataClass = $dataClass;
	}

	public function getFields() : array
	{
		return $this->getTableFields();
	}

	protected function getTableFields(array $config = []) : array
	{
		$result = [];

		foreach ($this->dataClass::getMap() as $field)
		{
			if (!($field instanceof ORM\Fields\ScalarField)) { continue; }

			$name = $field->getName();

			if (isset($config['INCLUDE']) && !in_array($name, $config['INCLUDE'], true)) { continue; }
			if (isset($config['EXCLUDE']) && in_array($name, $config['EXCLUDE'], true)) { continue; }

			$description = $this->describeTableField($field);

			if (isset($config['OVERRIDES'][$name]))
			{
				$description = $config['OVERRIDES'][$name] + $description;
			}

			$result[$name] = $description;
		}

		return $result;
	}

	protected function describeTableField(ORM\Fields\ScalarField $field) : array
	{
		$type = $this->resolveTableFieldType($field);
		$userField = [
			'MANDATORY' => ($field->isRequired() ? 'Y' : 'N'),
			'EDIT_IN_LIST' => $field->isAutocomplete() ? 'N' : 'Y',
			'SETTINGS' => [
				'DEFAULT_VALUE' => $field->getDefaultValue(),
			],
		];
		$userField += $this->getFieldDefaults($field->getName(), $type);

		if ($field instanceof ORM\Fields\EnumField)
		{
			$userField['VALUES'] = [];

			foreach ($field->getValues() as $option)
			{
				$userField['VALUES'][] = [
					'ID' => $option,
					'VALUE' => $this->getOptionTitle($field->getName(), $option),
				];
			}
		}

		return $userField;
	}

	protected function resolveTableFieldType(ORM\Fields\ScalarField $field) : string
	{
		$types = [
			ORM\Fields\EnumField::class => 'enumeration',
			ORM\Fields\DateField::class => 'date',
			ORM\Fields\DatetimeField::class => 'datetime',
			ORM\Fields\IntegerField::class => 'integer',
			ORM\Fields\FloatField::class => 'double',
			ORM\Fields\BooleanField::class => 'boolean',
			ORM\Fields\StringField::class => 'string',
		];
		$result = null;

		foreach ($types as $className => $type)
		{
			if (!($field instanceof $className)) { continue; }

			$result = $type;
			break;
		}

		Assert::notNull($result, sprintf('field[%s][userType]', $field->getName()));

		return $result;
	}

	protected function getFieldDefaults(string $name, string $type = 'string') : array
	{
		return [
			'TYPE' => $type,
			'FIELD_NAME' => $name,
			'LIST_COLUMN_LABEL' => $this->getFieldTitle($name),
			'MANDATORY' => 'N',
			'MULTIPLE' => 'N',
			'EDIT_IN_LIST' => 'N',
		];
	}

	protected function getFieldTitle(string $field) : string
	{
		if (method_exists(static::class, 'getMessage'))
		{
			$langKey = sprintf('FIELD_%s', mb_strtoupper($field));
			/** @noinspection PhpUndefinedMethodInspection */
			$result = static::getMessage($langKey, null, $field);
		}
		else
		{
			$result = $field;
		}

		return $result;
	}

	protected function getOptionTitle(string $field, string $option) : string
	{
		if (method_exists(static::class, 'getMessage'))
		{
			$langKey = sprintf('FIELD_%s_OPTION_%s', mb_strtoupper($field), mb_strtoupper($option));
			/** @noinspection PhpUndefinedMethodInspection */
			$result = static::getMessage($langKey, null, $option);
		}
		else
		{
			$result = $option;
		}

		return $result;
	}

	public function getEnvironment() : Entity\Reference\Environment
	{
		if ($this->environment === null)
		{
			$this->environment = Entity\Registry::getEnvironment();
		}

		return $this->environment;
	}
}