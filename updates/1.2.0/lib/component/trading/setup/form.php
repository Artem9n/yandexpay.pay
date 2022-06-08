<?php

namespace YandexPay\Pay\Component\Trading\Setup;

use Bitrix\Main;
use YandexPay\Pay;

class Form extends Pay\Component\Model\Form
{
	protected function save(Main\ORM\Objectify\EntityObject $model) : Main\ORM\Data\Result
	{
		$saveResult = $model->save();

		if ($model instanceof Pay\Trading\Setup\Model && $saveResult->isSuccess())
		{
			$model->install();
		}

		return $saveResult;
	}
}