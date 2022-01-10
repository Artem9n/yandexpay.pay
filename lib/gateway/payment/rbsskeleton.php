<?php

namespace Yandexpay\Pay\Gateway\Payment;

use Bitrix\Main;
use Bitrix\Currency;
use Bitrix\Main\Web;
use Yandexpay\Pay\Gateway;
use Yandexpay\Pay\Reference\Concerns;
use Yandexpay\Pay\Exceptions;

abstract class RbsSkeleton extends Gateway\Base
{
	use Concerns\HasMessage;

	protected const STATUS_FAILED = false;
	protected const STATUS_SUCCESS = 2;

	protected static $currencyMap = [];

	public function getGatewayId() : string
	{
		return 'rbs';
	}

	public function extraParams(): array
	{
		return [
			'PAYMENT_GATEWAY_USERNAME' => [
				'NAME' => static::getMessage('USERNAME'),
				'GROUP' => $this->getName(),
				'SORT' => 650,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => 40,
				],
			],
			'PAYMENT_GATEWAY_PASSWORD' => [
				'NAME' => static::getMessage('MERCHANT_PASSWORD'),
				'GROUP' => $this->getName(),
				'SORT' => 700,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => 40,
				],
			]
		];
	}

	public function getPaymentIdFromRequest(): ?int
	{
		return $this->request->get('paymentId');
	}

	public function startPay(): array
	{
		$result = [];

		if ($this->isSecure3ds())
		{
			$orderData = $this->getOrder();
			$orderId = $this->getOrderNumber($orderData);

			return [
				'PS_INVOICE_ID' => $orderId,
				'PS_SUM' => $this->getPaymentSum()
			];
		}

		$orderId = $this->buildRegisterOrder();
		$this->createPayment($orderId);

		return $result;
	}

	protected function createPayment(string $orderId) : void
	{
		$httpClient = new Web\HttpClient();
		$this->setHeaders($httpClient, 'json');
		$url = $this->getUrl('payment');

		$data = [
			'username' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'orderId' => $orderId,
			'paymentToken' => $this->getYandexToken(),
		];

		$resultData = $httpClient->post($url, Web\Json::encode($data));
		$resultData = $this->convertResult($resultData);

		$this->validate($resultData);
	}

	protected function buildRegisterOrder() : string
	{
		$registeredOrderId = $this->getRegisteredOrder();

		if ($registeredOrderId !== null) { return $registeredOrderId; }

		return $this->registerOrder();
	}

	protected function getOrder() : array
	{
		$httpClient = new Web\HttpClient();
		$this->setHeaders($httpClient);
		$url = $this->getUrl('order');

		$data = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'orderNumber' => $this->getExternalId(),
		];

		$resultData = $this->convertResult($httpClient->post($url, $data));
		$this->validate($resultData);

		return $resultData;
	}

	protected function getRegisteredOrder() : ?string
	{
		$httpClient = new Web\HttpClient();
		$this->setHeaders($httpClient);
		$url = $this->getUrl('order');

		$data = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'orderNumber' => $this->getExternalId(),
		];

		$resultData = $this->convertResult($httpClient->post($url, $data));

		if (!isset($resultData['orderStatus'])) { return null; }

		return $this->getOrderNumber($resultData);
	}

	protected function getOrderNumber(array $resultData) : ?string
	{
		$result = null;
		$attributes = $resultData['attributes'];

		foreach ($attributes as $value)
		{
			if ($value['name'] !== 'mdOrder') { continue; }

			$result = $value['value'];
			break;
		}

		return $result;
	}

	protected function validate(array $resultData) : void
	{
		if (
			isset($resultData['errorCode'], $resultData['errorMessage'])
			&& (int)$resultData['errorCode'] !== 0
		)
		{
			$message = $resultData['errorMessage'];
			throw new Main\SystemException($message);
		}

		if (isset($resultData['success']) && !(bool)$resultData['success'])
		{
			$message = $resultData['error']['message'];
			throw new Main\SystemException($message);
		}

		if (isset($resultData['success'], $resultData['data']['paReq']))
		{
			throw new Exceptions\Secure3dRedirect(
				$resultData['data']['acsUrl'],
				[
					'PaReq' => $resultData['data']['paReq'],
					'MD' => $resultData['data']['orderId'],
					'TermUrl' => $resultData['data']['termUrl']
				]
			);
		}

		if (
			isset($resultData['orderStatus'])
			&& $resultData['orderStatus'] !== static::STATUS_SUCCESS
		)
		{
			throw new Main\SystemException(self::getMessage('ERROR_' . $resultData['orderStatus']));
		}
	}

	protected function convertResult(string $data): array
	{
		return Main\Web\Json::decode($data);
	}

	protected function registerOrder() : string
	{
		$httpClient = new Web\HttpClient();
		$this->setHeaders($httpClient);
		$url = $this->getUrl('register');

		$data = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'amount' => $this->getPaymentAmount(),
			'orderNumber' => $this->getExternalId(),
			'returnUrl' => $this->getRedirectUrl(),
		];

		$resultData = $httpClient->post($url, $data);
		$resultData = $this->convertResult($resultData);
		$this->validate($resultData);

		return (string)$resultData['orderId'];
	}

	public function refund() : void
	{
		$httpClient = new Web\HttpClient();
		$this->setHeaders($httpClient);
		$url = $this->getUrl('refund');

		$dataRefund = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'orderId' => $this->getPaymentField('PS_INVOICE_ID'),
			'amount' => $this->getPaymentAmount(),
		];

		$resultData = $httpClient->post($url, $dataRefund);
		$resultData = $this->convertResult($resultData);
		$this->validate($resultData);
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