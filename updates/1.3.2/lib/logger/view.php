<?php
namespace YandexPay\Pay\Logger;

use YandexPay\Pay\Trading;
use YandexPay\Pay\Reference\Storage;
use YandexPay\Pay\Reference\Concerns;

class View extends Storage\View
{
	use Concerns\HasMessage;

	public function getFields() : array
	{
		$result = $this->getTableFields([
			'OVERRIDES' => [
				'LEVEL' => [
					'TYPE' => 'loglevel',
				],
				'MESSAGE' => [
					'TYPE' => 'logmessage'
				],
				'AUDIT' => [
					'TYPE' => 'logaudit'
				]
			],
		]);

		return $result;
	}
}