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

    protected function getUrlList(): array
    {
        $testUrl = 'https://web.rbsuat.com/ab';
        $activeUrl = 'https://pay.alfabank.ru/payment';

        return [
            'registration' => [
                static::TEST_URL => $testUrl . '/rest/register.do',
                static::ACTIVE_URL => $activeUrl . '/rest/register.do',
            ],

            'yandexpayment' => [
                static::TEST_URL => $testUrl . '/yandex/payment.do',
                static::ACTIVE_URL => $activeUrl . '/yandex/payment.do',
            ],

            'refund' => [
                static::TEST_URL => $testUrl . '/rest/refund.do',
                static::ACTIVE_URL => $activeUrl . '/rest/refund.do',
            ],
            'statusExtend' => [
                static::TEST_URL => $testUrl . '/rest/getOrderStatusExtended.do',
                static::ACTIVE_URL => $activeUrl . '/reset/getOrderStatusExtended.do',
            ]
        ];
    }
}