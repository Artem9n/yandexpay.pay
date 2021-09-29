<?php

namespace YandexPay\Pay\Component\Model;

use Bitrix\Main;
use YandexPay\Pay\Component;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Storage;

class Grid extends Component\Reference\Grid
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

	public function getFields(array $select = []) : array
	{
		$fields = $this->getTableFields();
		$result = !empty($select)
			? array_intersect_key($fields, array_flip($select))
			: $fields;

		$this->extendFieldsBySortable($result);

		return $result;
	}

	protected function getTableFields() : array
	{
		/** @var Storage\HasView $dataClass */
		$dataClass = $this->getDataClass();

		Assert::isSubclassOf($dataClass, Storage\HasView::class);

		return $dataClass::getView()->getFields();
	}

	protected function extendFieldsBySortable(array &$fields) : void
	{
		$scalarFields = $this->getScalarFields();

		foreach ($fields as $fieldName => &$field)
		{
			$field['SORTABLE'] = isset($scalarFields[$fieldName]);
		}
	}

	public function load(array $parameters = []) : array
	{
		$dataClass = $this->getDataClass();
		$parameters = $this->normalizeQueryParameters($parameters);
		$result = [];

		$query = $dataClass::getList($parameters);

		while ($item = $query->fetchObject())
		{
			$result[] = $item->collectValues();
		}

		return $result;
	}

	public function loadTotalCount(array $parameters = []) : ?int
	{
		$result = null;
		$dataClass = $this->getDataClass();
		$parameters = array_diff_key($parameters, [
			'select' => true,
			'limit' => true,
			'offset' => true,
			'order' => true,
		]);
		$parameters = $this->normalizeQueryParameters($parameters);
		$parameters['select'] = [ 'CNT' ];
		$parameters['runtime'] = array_merge($parameters['runtime'] ?? [], [
			new Main\Entity\ExpressionField('CNT', 'COUNT(1)'),
		]);

		$query = $dataClass::getList($parameters);

		if ($row = $query->fetch())
		{
			$result = (int)$row['CNT'];
		}

		return $result;
	}

	public function processAjaxAction(string $action, array $data) : void
	{
		$dataClass = $this->getDataClass();
		$selectedIds = $this->getActionSelectedIds($data);

		if (empty($selectedIds)) { return; }

		$collectionClass = $dataClass::getCollectionClass();
		$collection = $collectionClass::wakeUp($selectedIds);
		$method = $action . 'Action';

		foreach ($collection as $model)
		{
			Assert::methodExists($model, $method);

			$model->{$method}();
		}
	}

	protected function getActionSelectedIds($data) : array
	{
		if (!empty($data['IS_ALL']))
		{
			$parameters = [
				'select' => [ 'ID' ],
			];

			if (!empty($data['FILTER']))
			{
				$parameters['filter'] = $this->normalizeQueryFilter((array)$data['FILTER']);
			}

			$items = $this->load($parameters);
			$result = array_column($items, 'ID');
		}
		else
		{
			$result = (array)$data['ID'];
		}

		return $result;
	}

	/** @return Main\Entity\DataManager */
	protected function getDataClass() : string
	{
		return $this->getComponentParam('DATA_CLASS_NAME');
	}

	protected function normalizeQueryParameters(array $parameters) : array
	{
		if (isset($parameters['filter']))
		{
			$parameters['filter'] = $this->normalizeQueryFilter((array)$parameters['filter']);
		}

		if (isset($parameters['order']))
		{
			$parameters['order'] = $this->normalizeQueryOrder((array)$parameters['order']);
		}

		if (isset($parameters['select']))
		{
			$parameters['select'] = $this->normalizeQuerySelect((array)$parameters['select']);
		}

		return $parameters;
	}

	protected function normalizeQuerySelect(array $select) : array
	{
		$scalarFields = $this->getScalarFields();
		$result = [];

		foreach ($select as $fieldName)
		{
			if (isset($scalarFields[$fieldName]))
			{
				$result[] = $fieldName;
			}
		}

		return $result;
	}

	protected function normalizeQueryFilter(array $filter) : array
	{
		$scalarFields = $this->getScalarFields();
		$newFilter = $filter;

		foreach ($filter as $filterName => $filterValue)
		{
			if (!is_numeric($filterName))
			{
				$fieldName = $filterName;

				if (preg_match('/^[^A-Za-z]+(.+)$/', $filterName, $match))
				{
					$fieldName = $match[1];
				}

				if (!isset($scalarFields[$fieldName]) && !preg_match('/\.ID$/', $fieldName))
				{
					$newFilter[$filterName . '.ID'] = $filterValue;
					unset($newFilter[$fieldName]);
				}
			}
		}

		return $newFilter;
	}

	protected function normalizeQueryOrder(array $order) : array
	{
		$scalarFields = $this->getScalarFields();
		$newOrder = $order;

		foreach ($order as $fieldName => $orderDirection)
		{
			if (!isset($scalarFields[$fieldName]) && !preg_match('/\.ID$/', $fieldName))
			{
				$newOrder[$fieldName . '.ID'] = $orderDirection;
				unset($newOrder[$fieldName]);
			}
		}

		return $newOrder;
	}

	protected function getScalarFields() : array
	{
		$dataClass = $this->getDataClass();

		return $dataClass::getEntity()->getScalarFields();
	}
}