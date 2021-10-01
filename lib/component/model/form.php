<?php

namespace YandexPay\Pay\Component\Model;

use Bitrix\Main;
use YandexPay\Pay\Component;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Storage;

class Form extends Component\Reference\Form
{
	public function prepareComponentParams(array $params) : array
	{
		$params['DATA_CLASS_NAME'] = trim($params['DATA_CLASS_NAME']);

		return $params;
	}

	public function getRequiredParams() : array
	{
		return [
			'DATA_CLASS_NAME',
		];
	}

	public function modifyRequest(array $request, $fields) : array
	{
		$result = $request;

		foreach ($fields as $fieldName => $field)
		{
			if (!isset($request[$fieldName], $field['USER_TYPE']['BASE_TYPE'])) { continue; }

			if ($field['USER_TYPE']['BASE_TYPE'] === 'datetime')
			{
				$value = trim($request[$fieldName]);

				if ($value !== '')
				{
					$result[$fieldName] = new Main\Type\DateTime($value);
				}
				else
				{
					$result[$fieldName] = null;
				}
			}
		}

		return $result;
	}

	public function processPostAction(string $action, array $data) : void
	{
		$dataClass = $this->getDataClass();
		$primary = $data['PRIMARY'] ?? null;

		Assert::notNull($primary, 'data[PRIMARY]');

		$model = $dataClass::wakeUpObject($primary);
		$method = $action . 'Action';

		Assert::methodExists($model, $method);

		$model->{$method}();
	}

	public function getFields(array $select = [], array $item = null) : array
	{
		$fields = $this->getTableFields();

		return !empty($select)
			? array_intersect_key($fields, array_flip($select))
			: $fields;
	}

	protected function getTableFields() : array
	{
		/** @var Storage\HasView $dataClass */
		$dataClass = $this->getDataClass();

		Assert::isSubclassOf($dataClass, Storage\HasView::class);

		return $dataClass::getView()->getFields();
	}

	public function load($primary, array $select = [], bool $isCopy = false) : array
	{
		$dataClass = $this->getDataClass();
		$query = $dataClass::getByPrimary($primary);
		$result = $query->fetch();

		if (!$result)
		{
			throw new Main\SystemException($this->getComponentLang('ITEM_NOT_FOUND'));
		}

		if ($isCopy)
		{
			unset($result['ID']);
		}

		return $result;
	}

	public function validate(array $data, array $fields = null) : Main\Result
	{
		$primary = $data['PRIMARY'] ?? null;
		$sanitizedData = array_diff_key($data, [ 'PRIMARY' => true ]);
		$dataClass = $this->getDataClass();
		$validateResult = new Main\Entity\Result();

		$dataClass::checkFields($validateResult, $primary, $sanitizedData);

		if ($fields === null) { return $validateResult; }

		$result = new Main\Entity\Result();
		$fieldsMap = array_column($fields, 'FIELD_NAME', 'FIELD_NAME');

		foreach ($validateResult->getErrors() as $error)
		{
			$entityField = $error->getField();
			$fieldName = $entityField->getName();

			if (!isset($fieldsMap[$fieldName])) { continue; }

			$result->addError($error);
		}

		return $result;
	}

	public function add(array $values) : Main\Entity\AddResult
	{
		$dataClass = $this->getDataClass();
		$modelClass = $dataClass::getObjectClass();
		$model = new $modelClass();

		foreach ($values as $name => $value)
		{
			if ($name === 'ID') { continue; }

			$model->set($name, $value);
		}

		return $model->save();
	}

	public function update($primary, array $values) : Main\Entity\UpdateResult
	{
		$dataClass = $this->getDataClass();
		$modelClass = $dataClass::getObjectClass();
		$model = $modelClass::wakeUp($primary);

		foreach ($values as $name => $value)
		{
			if ($name === 'ID') { continue; }

			$model->set($name, $value);
		}

		return $model->save();
	}

	/** @return Main\ORM\Data\DataManager  */
	protected function getDataClass() : string
	{
		return $this->getComponentParam('DATA_CLASS_NAME');
	}
}