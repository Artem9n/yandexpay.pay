<?php

namespace YandexPay\Pay\Trading\Setup;

use YandexPay\Pay\Reference\Storage;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Ui\Userfield;

class View extends Storage\View
{
	use Concerns\HasMessage;

	public function getFields() : array
	{
		return $this->getTableFields([
			'OVERRIDES' => [
				'SITE_ID' => [
					'USER_TYPE' => Userfield\Registry::getUserType('enumeration'),
					'VALUES' => [
						[
							'ID' => 's1',
							'VALUE' => 's1',
						],
						[
							'ID' => 's2',
							'VALUE' => 's2',
						],
					], // todo
				],
				'PERSON_TYPE_ID' => [
					'USER_TYPE' => Userfield\Registry::getUserType('enumeration'),
					'VALUES' => [
						[
							'ID' => '1',
							'VALUE' => 'Individual',
						],
						[
							'ID' => '2',
							'VALUE' => 'Legal',
						],
					], // todo
				],
			],
		]);
	}
}