<?php

namespace Yandexpay\Pay\GateWay\Payment;

use Bitrix\Currency\CurrencyClassifier;
use Bitrix\Main;
use Bitrix\Main\Request;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Payment;
use Yandexpay\Pay\GateWay\Base;
use Yandexpay\Pay\Reference\Concerns\HasMessage;

class alfabank extends Base
{
    use HasMessage;


    protected $sort = 400;
    protected const STATUS_FAILED = false;
    protected const STATUS_SUCCSES = 2;

    protected static $currencyMap = [
        'RUB' => 810
    ];

    public function getId(): string
    {
        return 'alfabank';
    }

    public function getName(): string
    {
        return 'Rbs';
    }

    public function extraParams(string $code = ''): array
    {
        return [
            $code . '_PAYMENT_GATEWAY_USERNAME' => [
                'NAME' => static::getMessage('USERNAME'),
                'GROUP' => $this->getName(),
                'SORT' => 650
            ],
            $code . '_PAYMENT_GATEWAY_PASSWORD' => [
                'NAME' => static::getMessage('MERCHANT_PASSWORD'),
                'DESCRIPTION' => static::getMessage('MERCHANT_PASSWORD_DESCRIPTION'),
                'GROUP' => $this->getName(),
                'SORT' => 700
            ]
        ];
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

    public function getPaymentIdFromRequest(): ?int
    {
        return $this->request->get('paymentId');
    }

    public function startPay(): array
    {
        if($this->request->get('orderId') !== null)
        {
            $orderStatus = $this->statusExtend();
            $orderId = $orderStatus['attributes']['0']['value'];
            $result = [
                'PS_INVOICE_ID' => $orderId,
                'PS_SUM' => $this->getPaymentSum()
            ];

            if ($orderStatus['orderStatus'] === self::STATUS_SUCCSES) {
                return $result;
            }

        } else {
            $regOrder = $this->registerOrder();
            $orderId = $regOrder['orderId'];
            $result = [
                'PS_INVOICE_ID' => $orderId,
                'PS_SUM' => $this->getPaymentSum()
            ];
        }

        $data = $this->buildDataResource($orderId);
        $this->yandexPayment($data);

        return $result;
    }

    protected function registerOrder()
    {
        $httpClient = new HttpClient();
        $httpClient->setHeaders($this->getHeader(""));
        $url = $this->getUrl('registration');
        $data =
            [
                'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
                'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
                'amount' => $this->getPaymentAmount(),
                'currency' => $this->getCurrencyFormatted($this->getPaymentField('CURRENCY')),
                'orderNumber' => $this->getExternalId(),
                'returnUrl' => $this->getBackUrl(),

            ];
        $httpClient->post($url, $data);
        return Main\Web\Json::decode($httpClient->getResult());

    }

    protected function buildDataResource($orderID)
    {
        $buildData = [
            "username" => $this->getParameter("PAYMENT_GATEWAY_USERNAME"),
            "password" => $this->getParameter("PAYMENT_GATEWAY_PASSWORD"),
            "orderId" => $orderID,
            "paymentToken" => $this->getYandexToken()
        ];
        return Main\Web\Json::encode($buildData);
    }

    protected function yandexPayment($data)
    {
        $httpClient = new HttpClient();
        $httpClient->setHeaders($this->getHeader('json'));
        $urlYandex = $this->getUrl('yandexpayment');
        $httpClient->post($urlYandex, $data);
        $resultPay = Main\Web\Json::decode($httpClient->getResult());
        $this->checkResult($resultPay, $httpClient->getStatus());

    }

    protected function checkResult(array $resultData, int $status): void
    {
        if (!empty($resultData['data']['paReq'])) {
            throw new \Yandexpay\Pay\Exceptions\Secure3dRedirect(
                $resultData['data']['acsUrl'],
                [
                    'PaReq' => $resultData['data']['paReq'],
                    'MD' => $resultData['data']['orderId'],
                    'TermUrl' => $resultData['data']['termUrl']
                ]
            );
        }

        if ($resultData['success'] === self::STATUS_FAILED) {
            $message = $resultData['errorCode'] ? $resultData['errorMessage'] : $resultData['error']['message'];
            throw new Main\SystemException('' . $message);
        }

        if ($resultData['orderStatus'] != self::STATUS_SUCCSES) {
            throw new Main\SystemException(self::getMessage('ERROR_' . $resultData['orderStatus']));
        }

        if ($status != 200) {
            throw new Main\SystemException('Error status code: ' . $status);
        }
    }

    public function refund(): void
    {
        $dataRefund =
            [
                'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
                'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
                'orderId' => $this->getPaymentField('PS_INVOICE_ID'),
                'amount' => $this->getPaymentAmount(),
            ];

        $httpClient = new HttpClient();
        $httpClient->setHeaders($this->getHeader(""));
        $url = $this->getUrl('refund');
        $httpClient->post($url, $dataRefund);
        $requestSecurity = Main\Web\Json::decode($httpClient->getResult());

        $this->checkResult($requestSecurity, $httpClient->getStatus());

    }

    protected function statusExtend()
    {
        $httpClient = new HttpClient();
        $data = [
            "userName" => $this->getParameter("PAYMENT_GATEWAY_USERNAME"),
            "password" => $this->getParameter("PAYMENT_GATEWAY_PASSWORD"),
            "orderNumber" => $this->getExternalId(),
        ];
        $url = $this->getUrl('statusExtend');
        $httpClient->setHeaders($this->getHeader());
        $httpClient->post($url, $data);
        $resultStatus = Main\Web\Json::decode($httpClient->getResult());
        $this->checkResult($resultStatus, $httpClient->getStatus());

        return $resultStatus;
    }

    protected function getHeader($params = ''): array
    {
        if ($params == "json") {
            return ['Content-type' => 'application/json'];
        }
        return ['Content-type' => 'application/x-www-form-urlencoded'];
    }

    protected function getCurrencyFormatted(string $code): int
    {
        $currency = CurrencyClassifier::getCurrency($code, []);
        return self::$currencyMap[$code] ?? $currency['NUM_CODE'];
    }

}