<?php

namespace Yandexpay\Pay\Gateway\Payment;

use Bitrix\Main;
use Bitrix\Currency;
use Bitrix\Main\Web\HttpClient;
use Yandexpay\Pay\Gateway;
use Yandexpay\Pay\Reference\Concerns;

abstract class RbsSkeleton extends Gateway\Base
{
	use Concerns\HasMessage;

	protected const STATUS_FAILED = false;
	protected const STATUS_SUCCESS = 2;

	protected static $currencyMap = [];

	public function extraParams(): array
	{
		return [
			'PAYMENT_GATEWAY_USERNAME' => [
				'NAME' => static::getMessage('USERNAME'),
				'GROUP' => $this->getName(),
				'SORT' => 650
			],
			'PAYMENT_GATEWAY_PASSWORD' => [
				'NAME' => static::getMessage('MERCHANT_PASSWORD'),
				'DESCRIPTION' => static::getMessage('MERCHANT_PASSWORD_DESCRIPTION'),
				'GROUP' => $this->getName(),
				'SORT' => 700
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
		$dataRefund =
			[
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

}