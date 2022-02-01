<?php

namespace Yandexpay\Pay\Gateway\Payment;

use Bitrix\Main;
use Bitrix\Currency;
use Bitrix\Main\Web;
use Yandexpay\Pay\Gateway;
use YandexPay\Pay\Gateway\Manager;
use Yandexpay\Pay\Reference\Concerns;
use Yandexpay\Pay\Exceptions;

abstract class RbsSkeleton extends Gateway\Base
{
	use Concerns\HasMessage;

	protected const STATUS_FAILED = false;
	protected const STATUS_SUCCESS = 2;
	protected const THREE_DS_VERSION_2 = 'is3DSVer2';

	protected static $currencyMap = [];

	public function getGatewayId() : string
	{
		return Manager::RBS;
	}

	public function getName() : string
	{
		return static::getMessage('NAME');
	}

	public function getDescription() : string
	{
		return static::getMessage('DESCRIPTION');
	}

	public function getMerchantId() : ?string
	{
		$merchant = parent::getMerchantId();

		if ($merchant !== null) { return $merchant; }

		$login = $this->getParameter('PAYMENT_GATEWAY_USERNAME');

		$merchant = str_replace(self::getMessage('SYMBOL_MERCHANT'), '', $login);

		return $merchant;
	}

	protected function getTestUrl() : string
	{
		return '';
	}

	protected function getActiveUrl() : string
	{
		return '';
	}

	protected function getUrlList(): array
	{
		$testUrl = $this->getTestUrl();
		$activeUrl = $this->getActiveUrl();

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
				static::ACTIVE_URL => $activeUrl . '/rest/getOrderStatusExtended.do',
			],
			'finish' => [
				static::TEST_URL => $testUrl . '/rest/finish3dsVer2.do',
				static::ACTIVE_URL => $activeUrl . '/rest/finish3dsVer2.do',
			]
		];
	}

	public function extraParams(): array
	{
		return [
			'PAYMENT_GATEWAY_USERNAME' => [
				'NAME' => self::getMessage('USERNAME'),
				'GROUP' => $this->getName(),
				'SORT' => 650,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => 40,
				],
			],
			'PAYMENT_GATEWAY_PASSWORD' => [
				'NAME' => self::getMessage('MERCHANT_PASSWORD'),
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
			if ($this->request->get('version') == '2')
			{
				$this->createPaymentFinish($this->request->get('tDsTransId'));
			}

			$orderData = $this->getOrder();
			$orderId = $this->getOrderNumber($orderData);

			return [
				'PS_INVOICE_ID' => $orderId,
				'PS_SUM' => $this->getPaymentSum()
			];
		}

		$orderId = $this->buildRegisterOrder();

		$this->payment->setField('PS_INVOICE_ID', $orderId);

		$this->createPayment($orderId);

		return $result;
	}

	protected function createPaymentFinish(string $transId) : void
	{
		$httpClient = new Web\HttpClient();
		$url = $this->getUrl('finish');

		$data = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'tDsTransId' => $transId,
		];

		if (!$httpClient->post($url, $data))
		{
			throw new Main\SystemException('error finish pay');
		}
	}

	protected function createPayment(string $orderId) : void
	{
		$httpClient = new Web\HttpClient();
		$this->setHeaders($httpClient, 'json');
		$url = $this->getUrl('payment');

		$data = $this->request->get('secure') ?? [
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
		$orderId = $this->payment->getField('PS_INVOICE_ID');
		$httpClient = new Web\HttpClient();
		$this->setHeaders($httpClient);
		$url = $this->getUrl('order');

		$data = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'orderId' => $orderId,
		];

		$resultData = $this->convertResult($httpClient->post($url, $data));
		$this->validate($resultData);

		return $resultData;
	}

	protected function getRegisteredOrder() : ?string
	{
		$orderId = $this->payment->getField('PS_INVOICE_ID');

		if ($orderId === null) { return null; }

		$httpClient = new Web\HttpClient();
		$this->setHeaders($httpClient);
		$url = $this->getUrl('order');

		$data = [
			'userName' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
			'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
			'orderId' => $orderId,
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

		if (isset($resultData['success']))
		{
			$this->resultSecure($resultData['data']);
		}

		if (
			isset($resultData['orderStatus'])
			&& $resultData['orderStatus'] !== static::STATUS_SUCCESS
		)
		{
			throw new Main\SystemException(self::getMessage('ERROR_' . $resultData['orderStatus']));
		}
	}

	protected function resultSecure(array $resultData) : void
	{
		$methods = [
			'secureAuthVersion2',
			'securePayVersion2',
			'securePayVersion1'
		];

		foreach ($methods as $methodName)
		{
			if (!method_exists($this, $methodName)) { continue; }

			$this->{$methodName}($resultData);
		}
	}

	protected function secureAuthVersion2(array $data) : void
	{
		if (
			isset($data[self::THREE_DS_VERSION_2], $data['threeDSMethodURLServer'])
			&& (bool)$data[self::THREE_DS_VERSION_2]
		)
		{
			$params = [
				'secure' => [
					'username' => $this->getParameter('PAYMENT_GATEWAY_USERNAME'),
					'password' => $this->getParameter('PAYMENT_GATEWAY_PASSWORD'),
					'paymentToken' => $this->getYandexToken(),
					'orderId' => $data['orderId'],
					'threeDSServerTransId' => $data['threeDSServerTransId'],
					'threeDSVer2FinishUrl' => $this->getRedirectUrl(['version' => '2', 'tDsTransId' => $data['threeDSServerTransId']]),
				],
				'notify' => [
					'externalId' => $this->getPaymentId(),
					'paySystemId' => $this->payment->getPaymentSystemId(),
				]
			];

			throw new Exceptions\Secure3dRedirect(
				$data['threeDSMethodURLServer'],
				$params,
				false,
				'POST',
				'iframerbs'
			);
		}
	}

	protected function securePayVersion2(array $data) : void
	{
		if (
			isset($data[self::THREE_DS_VERSION_2], $data['acsUrl'], $data['packedCReq'])
			&& (bool)$data[self::THREE_DS_VERSION_2]
		)
		{
			$params = [
				'creq' => $data['packedCReq']
			];

			throw new Exceptions\Secure3dRedirect(
				$data['acsUrl'],
				$params
			);
		}
	}

	protected function securePayVersion1(array $data) : void
	{
		if(isset($data['paReq'], $data['acsUrl']) && !(bool)$data[self::THREE_DS_VERSION_2])
		{
			$params = [
				'PaReq' => $data['paReq'],
				'MD' => $data['orderId'],
				'TermUrl' => $data['termUrl']
			];

			throw new Exceptions\Secure3dRedirect(
				$data['acsUrl'],
				$params
			);
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
			'description' => $this->convertEncoding(self::getMessage('ORDER_DESCRIPTION', ['#ORDER_ID#' => $this->getOrderId()])),
		];

		$resultData = $httpClient->post($url, $data);
		$resultData = $this->convertResult($resultData);
		$this->validate($resultData);

		return (string)$resultData['orderId'];
	}

	protected function convertEncoding(string $message) : string
	{
		$isUtf8Config = Main\Application::isUtfMode();

		if ($isUtf8Config) { return $message; }

		return Main\Text\Encoding::convertEncoding($message, 'WINDOWS-1251', 'UTF-8');
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