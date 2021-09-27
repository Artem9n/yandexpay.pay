<?php

namespace YandexPay\Pay\GateWay\Payment;

use Bitrix\Currency\CurrencyClassifier;
use Bitrix\Main;
use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Main\Web\HttpClient;
use YandexPay\Pay\GateWay\Base;
use YandexPay\Pay\Reference\Concerns\HasMessage;

class Best2Pay extends Base
{
	use HasMessage;

	protected const CODE_SKIP_ORDER = 109;
	protected const CODE_NOT_ORDER = 104;

	protected const ACTION_PAY = 'pay';

	protected $sort = 300;

	public function getId() : string
	{
		return 'best2pay';
	}

	public function getName() : string
	{
		return 'Best2pay';
	}

	protected function getUrlList() : array
	{
		return [
			'register' => [
				static::TEST_URL    => 'https://test.best2pay.net/webapi/Register',
				static::ACTIVE_URL  => 'https://pay.best2pay.net/webapi/Register'
			],
			'purchase' => [
				static::TEST_URL    => 'https://test.best2pay.net/webapi/Purchase',
				static::ACTIVE_URL  => 'https://pay.best2pay.net/webapi/Purchase'
			],
			'order' => [
				static::TEST_URL    => 'https://test.best2pay.net/webapi/Order',
				static::ACTIVE_URL  => 'https://pay.best2pay.net/webapi/Order'
			]
		];
	}

	protected function getHeaders(): array
	{
		return [
			'Content-type' => 'application/x-www-form-urlencoded'
		];
	}

	public function extraParams(string $code = '') : array
	{
		return [
			$code . '_PAYMENT_GATEWAY_SECTOR_ID' => [
				'NAME'      => static::getMessage('SECTOR_ID'),
				'GROUP'     => $this->getName(),
				'SORT'      => 650
			],
			$code . '_PAYMENT_GATEWAY_PASSWORD' => [
				'NAME'          => static::getMessage('MERCHANT_PASSWORD'),
				'DESCRIPTION'   => static::getMessage('MERCHANT_PASSWORD_DESCRIPTION'),
				'GROUP'         => $this->getName(),
				'SORT'          => 700
			]
		];
	}

	public function startPay() : array
	{
		$result = [];

		$orderData = $this->buildRegisterOrder();

		$this->createPurchase($orderData['order']['id']);

		return [];
	}

	protected function createPurchase(int $orderId)
	{
		$result = [];

		$httpClient = new HttpClient();

		$httpClient->setHeaders($this->getHeaders());

		$url = $this->getUrl('purchase');

		$sector = (int)$this->getParameter('PAYMENT_GATEWAY_SECTOR_ID');
		$password = $this->getParameter('PAYMENT_GATEWAY_PASSWORD');
		$signature = $this->getSignature([$sector, $orderId, $password]);
		$yandexData = $this->request->get('yandexData');

		$data = [
			'sector'            => $sector,
			'id'                => $orderId,
			'signature'         => $signature,
			'yandexCryptogram'  => $yandexData['token'],
			'action'            => self::ACTION_PAY
		];
		pr($data);
		$httpClient->post($url, $data);

		pr(Main\Text\Encoding::convertEncodingToCurrent($httpClient->getResult()));
		pr($httpClient->getEffectiveUrl());

		die;
	}

	protected function buildRegisterOrder(): array
	{
		$registredOrder = $this->getRegistredOrder();

		if (!empty($registredOrder)) { return $registredOrder; }

		return $this->registerOrder();
	}

	protected function registerOrder(): array
	{
		$httpClient = new HttpClient();

		$httpClient->setHeaders($this->getHeaders());

		$url = $this->getUrl('register');

		$data = $this->buildDataRegister();

		$httpClient->post($url, $data);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($httpClient, $result);

		return $result;
	}

	protected function getRegistredOrder(): array
	{
		$httpClient = new HttpClient();

		$httpClient->setHeaders($this->getHeaders());

		$url = $this->getUrl('order');

		$sector = (int)$this->getParameter('PAYMENT_GATEWAY_SECTOR_ID');
		$orderId = $this->getOrderId();
		$password = $this->getParameter('PAYMENT_GATEWAY_PASSWORD');

		$data = [
			'sector'    => $sector,
			'signature' => $this->getSignature([$sector, $orderId, $password]),
			'reference' => $orderId
		];

		$httpClient->post($url, $data);

		$resultData = $this->convertResultData($httpClient->getResult());

		if (
			isset($resultData['error'])
			&& (
				(int)$resultData['error']['code'] === self::CODE_SKIP_ORDER
				|| (int)$resultData['error']['code'] === self::CODE_NOT_ORDER
			)
		)
		{
			return [];
		}

		$this->checkResult($httpClient, $resultData);

		return $resultData;
	}

	protected function convertResultData($data): array
	{
		$result = [];

		$xmlData = new \SimpleXMLElement($data);
		$parentName = $xmlData->getName();

		foreach ($xmlData as $code => $value)
		{
			if ($code === 'parameters')
			{
				foreach ($value->attributes() as $attrName => $attribute)
				{
					$result[$code][$attrName] = (string)$attribute;
				}

				foreach ($value->children() as $child)
				{
					foreach ((array)$child as $childName => $childValue)
					{
						$result[$code][$childName] = $childValue;
					}
				}

				continue;
			}

			if ($code === 'description')
			{
				$value = Main\Text\Encoding::convertEncodingToCurrent((string)$value);
			}

			$result[$code] = (string)$value;
		}

		return [$parentName => $result];
	}

	protected function buildDataRegister(): array
	{
		$sector = $this->getParameter('PAYMENT_GATEWAY_SECTOR_ID');
		$password = $this->getParameter('PAYMENT_GATEWAY_PASSWORD');
		$amount = $this->getPaymentAmount();
		$currency = $this->getCurrencyFormatted($this->getPaymentField('CURRENCY'));
		$description =  Main\Text\Encoding::convertEncoding(
			static::getMessage('REGISTER_DESCRIPTION', ['#ORDER_ID#' => $this->getOrderId()]),
			'WINDOWS-1251',
			'UTF-8'
		);
		$reference = $this->getOrderId();
		$signature = $this->getSignature([$sector, $amount, $currency, $password]);

		return [
			'sector'        => $sector,
			'amount'        => $amount,
			'currency'      => $currency,
			'description'   => $description,
			'signature'     => $signature,
			'reference'     => $reference
		];
	}

	public function refund(): void
	{

	}

	protected function checkResult(HttpClient $httpClient, array $resultData): void
	{
		$errors = $httpClient->getError();

		if (!empty($errors))
		{
			throw new Main\SystemException($errors);
		}

		if (isset($resultData['error']))
		{
			throw new Main\SystemException(self::getMessage('ERROR_' . $resultData['error']['code']));
		}
	}

	public function getPaymentIdFromRequest() : ?int
	{
		// TODO: Implement getPaymentIdFromRequest() method.
	}

	protected function getSignature(array $params): string
	{
		$str = '';

		foreach ($params as $value)
		{
			$str .= $value;
		}

		return base64_encode(md5($str));
	}

	protected function getCurrencyFormatted(string $code): int
	{
		$currency = CurrencyClassifier::getCurrency($code, []);

		return (int)$currency['NUM_CODE'] ?: 643;
	}
}