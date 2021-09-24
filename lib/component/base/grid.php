<?php

namespace YandexPay\Pay\Component\Base;

abstract class Grid extends AbstractProvider
{
	abstract public function getDefaultSort() : array;

	abstract public function getDefaultFilter() : array;

	abstract public function getFields(array $select = []) : array;

	abstract public function load(array $queryParameters = []) : array;

	abstract public function loadTotalCount(array $queryParameters = []) : ?int;

	abstract public function deleteItem($id) : void;

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