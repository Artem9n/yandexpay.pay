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

			if ($orderStatus['orderStatus'] === self::STATUS_SUCCESS)
			{
				return $result;
			}

		}
		else
		{
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

	protected function registerOrder() : array
	{
		$httpClient = new HttpClient();
		$this->setHeaders($httpClient);
		$url = $this->getUrl('registration');

		$data = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'amount' => $this->getPaymentAmount(),
			'currency' => $this->getCurrencyFormatted($this->getPaymentField('CURRENCY')),
			'orderNumber' => $this->getExternalId(),
			'returnUrl' => $this->getRedirectUrl(),
		];

		$httpClient->post($url, $data);

		return Main\Web\Json::decode($httpClient->getResult());
	}

	protected function buildDataResource($orderID) : string
	{
		$buildData = [
			'username' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'orderId' => $orderID,
			'paymentToken' => $this->getYandexToken()
		];

		return Main\Web\Json::encode($buildData);
	}

	protected function yandexPayment($data) : void
	{
		$httpClient = new HttpClient();
		$this->setHeaders($httpClient, 'json');
		$urlYandex = $this->getUrl('yandexpayment');
		$httpClient->post($urlYandex, $data);
		$resultPay = Main\Web\Json::decode($httpClient->getResult());

		echo '<pre>';
		print_r($data);
		echo '</pre>';

		echo '<pre>';
		print_r(Main\Text\Encoding::convertEncodingToCurrent(Main\Web\Json::decode($httpClient->getResult())));
		echo '</pre>';
		die;

		$this->checkResult($resultPay, $httpClient->getStatus());
	}

	protected function checkResult(array $resultData, int $status) : void
	{
		if ($status !== 200)
		{
			throw new Main\SystemException('Error status code: ' . $status);
		}

		if (!empty($resultData['data']['paReq']))
		{
			throw new \Yandexpay\Pay\Exceptions\Secure3dRedirect(
				$resultData['data']['acsUrl'],
				[
					'PaReq' => $resultData['data']['paReq'],
					'MD' => $resultData['data']['orderId'],
					'TermUrl' => $resultData['data']['termUrl']
				]
			);
		}

		if (
			isset($resultData['success'])
			&& $resultData['success'] === self::STATUS_FAILED
		)
		{
			$message = $resultData['errorCode'] ? $resultData['errorMessage'] : $resultData['error']['message'];
			throw new Main\SystemException('' . $message);
		}

		if (
			isset($resultData['orderStatus'])
			&& $resultData['orderStatus'] !== self::STATUS_SUCCESS
		)
		{
			throw new Main\SystemException(self::getMessage('ERROR_' . $resultData['orderStatus']));
		}
	}

	public function refund() : void
	{
		$dataRefund = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'orderId' => $this->getPaymentField('PS_INVOICE_ID'),
			'amount' => $this->getPaymentAmount(),
		];

		$httpClient = new HttpClient();
		$this->setHeaders($httpClient);
		$url = $this->getUrl('refund');
		$httpClient->post($url, $dataRefund);
		$requestSecurity = Main\Web\Json::decode($httpClient->getResult());

		$this->checkResult($requestSecurity, $httpClient->getStatus());
	}

	protected function statusExtend() : array
	{
		$httpClient = new HttpClient();
		$data = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'orderNumber' => $this->getExternalId(),
		];
		$url = $this->getUrl('statusExtend');
		$this->setHeaders($httpClient);
		$httpClient->post($url, $data);
		$resultStatus = Main\Web\Json::decode($httpClient->getResult());
		$this->checkResult($resultStatus, $httpClient->getStatus());

		return $resultStatus;
	}

	protected function getHeaders(string $key = '') : array
	{
		if ($key === 'json')
		{
			return ['Content-type' => 'application/json'];
		}

		return ['Content-type' => 'application/x-www-form-urlencoded'];
	}

	protected function getCurrencyFormatted(string $code) : int
	{
		$currency = Currency\CurrencyClassifier::getCurrency($code, []);
		return self::$currencyMap[$code] ?? $currency['NUM_CODE'];
	}

	protected function isSecure3ds() : bool
	{
		return $this->request->get('secure3ds') !== null;
	}

}