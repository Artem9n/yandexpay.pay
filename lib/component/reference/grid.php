<?php

namespace YandexPay\Pay\Component\Reference;

abstract class Grid extends AbstractProvider
{
	abstract public function getFields(array $select = []) : array;

	abstract public function load(array $parameters = []) : array;

	abstract public function loadTotalCount(array $parameters = []) : ?int;

	public function getDefaultSort() : array
	{
		$param = $this->getComponentParam('DEFAULT_SORT');

		return (!empty($param) && is_array($param) ? $param : []);
	}

	public function getDefaultFilter() : array
	{
		$param = $this->getComponentParam('DEFAULT_FILTER');

		return (!empty($param) && is_array($param) ? $param : []);
	}

	public function filterActions(array $item, array $actions) : array
	{
		return $actions;
	}

	public function getContextMenu() : array
	{
		return [];
	}

	public function getGroupActions() : array
	{
		return [];
	}

	public function getUiGroupActions() : array
	{
		return $this->getGroupActions();
	}

	public function getGroupActionParams() : array
	{
		return [];
	}

	public function getUiGroupActionParams() : array
	{
		return $this->getGroupActionParams();
	}
}