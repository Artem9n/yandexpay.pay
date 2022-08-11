<?php

namespace YandexPay\Pay\Trading\Setup;

use YandexPay\Pay\Reference\Storage;
use YandexPay\Pay\Reference\Concerns;

class View extends Storage\View
{
	use Concerns\HasMessage;

	public function getFields() : array
	{
		$environment = $this->getEnvironment();

		$result = $this->getTableFields([
			'OVERRIDES' => [
				'SITE_ID' => [
					'TYPE' => 'enumeration',
					'VALUES' => $environment->getSite()->getOptions(),
				],
				'PERSON_TYPE_ID' => [
					'TYPE' => 'enumeration',
					'VALUES' => $environment->getPersonType()->getEnum(),
				],
			],
		]);

		$result['YANDEX_DELIVERY'] = [
			'TYPE' => 'yandexDelivery',
			'NAME' => static::getMessage('YANDEX_DELIVERY'),
		];

		return $result;
	}
}