<?php

namespace Yandexpay\Pay\Gateway\Payment;

class Alfabank extends RbsSkeleton
{
    public function getId(): string
    {
        return 'alfabank';
    }

    public function getName(): string
    {
        return 'Alfabank(Rbs)';
    }

	public function getGatewayId() : string
	{
		return 'alfabank';
	}

	protected function getUrlList(): array
    {
        $testUrl = 'https://web.rbsuat.com/ab';
        $activeUrl = 'https://pay.alfabank.ru/payment';

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