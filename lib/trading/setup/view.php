<?php

namespace YandexPay\Pay\Trading\Setup;

use YandexPay\Pay\Reference\Storage;
use YandexPay\Pay\Reference\Concerns;

class View extends Storage\View
{
	use Concerns\HasMessage;

	public function getFields() : array
	{
		return
			$this->getTableFields(['ID'])
			+ $this->getEnvironmentFields();
	}

	protected function getEnvironmentFields() : array
	{
		return [
			'SITE_ID' => $this->getFieldDefaults('SITE_ID', 'enumeration') + [
				'VALUES' => [], // todo
			],
			'PERSON_TYPE_ID' => $this->getFieldDefaults('PERSON_TYPE_ID', 'enumeration') + [
				'VALUES' => [], // todo
			],
		];
	}
}