<?php

namespace YandexPay\Pay\Component\Reference;

use Bitrix\Main;

abstract class Form extends AbstractProvider
{
	abstract public function modifyRequest(array $request, array $fields) : array;

	abstract public function getFields(array $select = [], array $item = null) : array;

	abstract public function load($primary, array $select = [], bool $isCopy = false) : array;

	abstract public function validate(array $data, array $fields = null) : Main\Result;

	abstract public function add(array $values) : Main\ORM\Data\AddResult;

	abstract public function update($primary, array $values) : Main\ORM\Data\UpdateResult;

	public function extend(array $data, array $select = []) : array
	{
		return $data;
	}

	public function processPostAction(string $action, array $data) : void
	{
		throw new Main\SystemException('ACTION_NOT_FOUND');
	}
}