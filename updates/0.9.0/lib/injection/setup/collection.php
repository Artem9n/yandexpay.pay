<?php

namespace YandexPay\Pay\Injection\Setup;

class Collection extends EO_Repository_Collection
{
	public function deactivate() : void
	{
		foreach ($this->getAll() as $model)
		{
			$model->deactivateAction();
		}
	}

	public function activate() : void
	{
		foreach ($this->getAll() as $model)
		{
			$model->activateAction();
		}
	}

	public function delete() : void
	{
		foreach ($this->getAll() as $model)
		{
			$model->deleteAction();
		}
	}
}