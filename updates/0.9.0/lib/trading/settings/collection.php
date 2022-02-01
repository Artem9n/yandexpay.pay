<?php

namespace YandexPay\Pay\Trading\Settings;

class Collection extends EO_Repository_Collection
{
	public function getValues() : array
	{
		$result = [];

		foreach ($this->getAll() as $model)
		{
			$result[$model->getName()] = $model->getValue();
		}

		return $result;
	}

	/** @return array<string, Model> */
	public function mapCollection() : array
	{
		$result = [];

		foreach ($this->getAll() as $model)
		{
			$result[$model->getName()] = $model;
		}

		return $result;
	}

	public function delete() : void
	{
		foreach ($this->getAll() as $model)
		{
			$model->delete();
		}
	}
}