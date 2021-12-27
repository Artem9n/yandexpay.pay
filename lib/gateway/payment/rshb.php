<?php

namespace Yandexpay\Pay\Gateway\Payment;

use Bitrix\Main;
use Bitrix\Currency;
use Bitrix\Main\Web\HttpClient;

class Rshb extends RbsSkeleton
{
	public function getId(): string
	{
		return 'rshb';
	}

	public function getName(): string
	{
		return 'Rosselkhozbank(Rbs)';
	}

	protected function getUrlList(): array
	{
		$testUrl = 'https://web.rbsuat.com/rshb/payment';
		$activeUrl = 'https://pay.alfabank.ru/payment'; // todo rshb

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