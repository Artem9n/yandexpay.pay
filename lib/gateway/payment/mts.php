<?php

namespace Yandexpay\Pay\Gateway\Payment;

use Bitrix\Main;
use Bitrix\Currency;
use Bitrix\Main\Web\HttpClient;

class Mts extends RbsSkeleton
{
	public function getId(): string
	{
		return 'mts';
	}

	public function getName(): string
	{
		return 'Mtsbank(Rbs)';
	}

	protected function getUrlList(): array
	{
		$testUrl = 'https://web.rbsuat.com/mtsbank';
		$activeUrl = 'https://pay.alfabank.ru/payment'; // todo mts

		return [
			'register' => [
				static::TEST_URL => $testUrl . '/rest/register.do',
				static::ACTIVE_URL => $activeUrl . '/rest/register.do',
			],

			'payment' => [
				static::TEST_URL => $testUrl . '/yandex/payment.do',
				static::ACTIVE_URL => $activeUrl . '/yandex/payment.do',
			],

			'refund' => [
				static::TEST_URL => $testUrl . '/rest/refund.do',
				static::ACTIVE_URL => $activeUrl . '/rest/refund.do',
			],
			'order' => [
				static::TEST_URL => $testUrl . '/rest/getOrderStatusExtended.do',
				static::ACTIVE_URL => $activeUrl . '/reset/getOrderStatusExtended.do',
			]
		];
	}
}