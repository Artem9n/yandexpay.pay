<?php

namespace YandexPay\Pay\Component\Model;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Component;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Storage;
use YandexPay\Pay\Utils;

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

		if (empty($select)) { return $fields; }

		return $this->selectFields($fields, $select);
	}

	protected function getTableFields() : array
	{
		/** @var Storage\HasView $dataClass */
		$dataClass = $this->getDataClass();

		Assert::isSubclassOf($dataClass, Storage\HasView::class);

		return $dataClass::getView()->getFields();
	}

	protected function selectFields(array $fields, array $select) : array
	{
		$map = array_flip($select);
		$result = [];

		foreach ($fields as $name => $field)
		{
			if (isset($map[$name]))
			{
				$match = true;
			}
			else
			{
				$match = false;
				$parts = Utils\BracketChain::splitKey($name);
				$partsTotal = count($parts);

				foreach (range(1, $partsTotal) as $sliceCount)
				{
					$slice = array_slice($parts, 0, $sliceCount);
					$sliceName = Utils\BracketChain::joinKey($slice);

					if (!isset($map[$sliceName])) { continue; }

					$match = true;
					break;
				}
			}

			if ($match)
			{
				$result[$name] = $field;
			}
		}

		return $result;
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

	public function add(array $values) : Main\ORM\Data\AddResult
	{
		$dataClass = $this->getDataClass();
		$modelClass = $dataClass::getObjectClass();
		$model = new $modelClass();

		$fields = $this->getComponentResult('FIELDS');

		$values = $this->sliceFieldsDependHidden($fields, $values);

		foreach ($values as $name => $value)
		{
			if ($name === 'ID') { continue; }

			$model->set($name, $value);
		}

		return $model->save();
	}

	public function update($primary, array $values) : Main\ORM\Data\UpdateResult
	{
		$dataClass = $this->getDataClass();
		$modelClass = $dataClass::getObjectClass();
		$model = $modelClass::wakeUp($primary);

		$fields = $this->getComponentResult('FIELDS');

		$values = $this->sliceFieldsDependHidden($fields, $values);

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

	protected function sliceFieldsDependHidden(array $fields, array $values) : array
	{
		$result = $values;

		foreach ($fields as $fieldName => $field)
		{
			if (empty($field['DEPEND_HIDDEN'])) { continue; }

			Pay\Utils\BracketChain::unset($result, $fieldName);
		}

		return $result;
	}
}