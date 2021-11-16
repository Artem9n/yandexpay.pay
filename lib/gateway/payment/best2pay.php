<?php

namespace YandexPay\Pay\Gateway\Payment;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Currency\CurrencyClassifier;
use YandexPay\Pay\Gateway;
use YandexPay\Pay\Reference\Concerns;

class Best2pay extends Gateway\Base
{
	use Concerns\HasMessage;

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
				static::ACTIVE_URL  => 'https://pay.best2pay.net/webapi/Register',
			],
			'purchase' => [
				static::TEST_URL    => 'https://test.best2pay.net/webapi/Purchase',
				static::ACTIVE_URL  => 'https://pay.best2pay.net/webapi/Purchase',
			],
			'order' => [
				static::TEST_URL    => 'https://test.best2pay.net/webapi/Order',
				static::ACTIVE_URL  => 'https://pay.best2pay.net/webapi/Order',
			],
			'refund' => [
				static::TEST_URL    => 'https://test.best2pay.net/webapi/Reverse',
				static::ACTIVE_URL  => 'https://pay.best2pay.net/webapi/Reverse',
			],
			'operation' => [
				static::TEST_URL    => 'https://test.best2pay.net/webapi/Operation',
				static::ACTIVE_URL  => 'https://pay.best2pay.net/webapi/Operation',
			]
		];
	}

	protected function getHeaders(string $key = ''): array
	{
		return [
			'Content-type' => 'application/x-www-form-urlencoded',
		];
	}

	public function extraParams(string $code = '') : array
	{
		return [
			$code . '_PAYMENT_GATEWAY_SECTOR_ID' => [
				'NAME'      => static::getMessage('SECTOR_ID'),
				'GROUP'     => $this->getName(),
				'SORT'      => 650,
			],
			$code . '_PAYMENT_GATEWAY_PASSWORD' => [
				'NAME'          => static::getMessage('MERCHANT_PASSWORD'),
				'DESCRIPTION'   => static::getMessage('MERCHANT_PASSWORD_DESCRIPTION'),
				'GROUP'         => $this->getName(),
				'SORT'          => 700,
			],
		];
	}

	public function startPay() : array
	{
		$orderData = $this->buildRegisterOrder();

		[$operation, $id, $reference] = $this->createPurchase($orderData['order']['id']);

		return [
			'PS_INVOICE_ID'     => $id . '#' . $reference . '#' . $operation,
			'PS_SUM'            => $this->getPaymentSum()
		];
	}

	protected function createPurchase(int $orderId) : array
	{
		$httpClient = new HttpClient();

		$this->setHeaders($httpClient);

		$url = $this->getUrl('purchase');

		$sector = (int)$this->getParameter('PAYMENT_GATEWAY_SECTOR_ID');
		$password = $this->getParameter('PAYMENT_GATEWAY_PASSWORD');
		$signature = $this->getSignature([$sector, $orderId, $password]);

		$data = [
			'sector'            => $sector,
			'id'                => $orderId,
			'signature'         => $signature,
			'yandexCryptogram'  => $this->getYandexToken(),
			'action'            => self::ACTION_PAY,
		];

		$httpClient->post($url, $data);
		pr($httpClient->getResult());
		pr($httpClient->getEffectiveUrl());
		die;
		/*pr($httpClient->getResult());
		pr($httpClient->getEffectiveUrl());
		die;*/
		//$this->getStatusPurchase($operation, $id);
		return $this->checkEffectiveUrl($httpClient->getEffectiveUrl());
	}

	protected function getStatusPurchase(int $operation, int $orderId)
	{
		$httpClient = new HttpClient();

		$this->setHeaders($httpClient);

		$url = $this->getUrl('operation');

		$sector = (int)$this->getParameter('PAYMENT_GATEWAY_SECTOR_ID');
		$password = $this->getParameter('PAYMENT_GATEWAY_PASSWORD');
		$signature = $this->getSignature([$sector, $orderId, $operation, $password]);

		$data = [
			'sector'    => $sector,
			'id'        => $orderId,
			'operation' => $operation,
			'signature' => $signature
		];

		$httpClient->post($url, $data);

		pr($httpClient->getResult());
		die;
	}

	protected function checkEffectiveUrl(string $url) : array
	{
		$parseUrl = parse_url($url, PHP_URL_QUERY);
		parse_str($parseUrl, $result);

		if (isset($result['code']))
		{
			throw new Main\SystemException(self::getMessage('ERROR_' . $result['code']));
		}

		if (isset($result['error']))
		{
			throw new Main\SystemException(self::getMessage('ERROR_' . $result['error']));
		}

		return [$result['operation'], $result['id'], $result['reference']];
	}

	protected function buildRegisterOrder(): array
	{
		$registeredOrder = $this->getRegisteredOrder();

		if (!empty($registeredOrder)) { return $registeredOrder; }

		return $this->registerOrder();
	}

	protected function registerOrder(): array
	{
		$httpClient = new HttpClient();

		$this->setHeaders($httpClient);

		$url = $this->getUrl('register');

		$data = $this->buildDataRegister();

		$httpClient->post($url, $data);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($httpClient, $result);

		return $result;
	}

	protected function getRegisteredOrder(): array
	{
		$httpClient = new HttpClient();

		$this->setHeaders($httpClient);

		$url = $this->getUrl('order');

		$sector = (int)$this->getParameter('PAYMENT_GATEWAY_SECTOR_ID');
		$orderId = $this->getExternalId();
		$password = $this->getParameter('PAYMENT_GATEWAY_PASSWORD');

		$data = [
			'sector'    => $sector,
			'signature' => $this->getSignature([$sector, $orderId, $password]),
			'reference' => $orderId,
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

		$jsonData = Main\Text\Encoding::convertEncodingToCurrent(Main\Web\Json::encode($xmlData));
		$result[$parentName] = Main\Web\Json::decode($jsonData);

		return $result;
		/*
		$parentName = $xmlData->getName();

		/*foreach ($xmlData as $code => $value)
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

		return [$parentName => $result];*/
	}

	protected function buildDataRegister(): array
	{
		$sector = $this->getParameter('PAYMENT_GATEWAY_SECTOR_ID');
		$password = $this->getParameter('PAYMENT_GATEWAY_PASSWORD');
		$amount = $this->getPaymentAmount();
		$currency = $this->getCurrencyFormatted($this->getPaymentField('CURRENCY'));
		$description = $this->convertEncoding(static::getMessage('REGISTER_DESCRIPTION', ['#ORDER_ID#' => $this->getOrderId()]));
		$reference = $this->getExternalId();
		$signature = $this->getSignature([$sector, $amount, $currency, $password]);

		return [
			'sector'        => $sector,
			'amount'        => $amount,
			'currency'      => $currency,
			'description'   => $description,
			'signature'     => $signature,
			'reference'     => $reference,
			'url'           => $this->getRedirectUrl(),
			'fiscal_positions' => $this->buildFiscalPosition()
		];
	}

	protected function buildFiscalPosition(string $separator = ';') : string
	{
		$items = $this->getItems();

		foreach($items as &$item)
		{
			$item = implode($separator, $item);

		}
		unset($item);

		return implode('|', $items);
	}

	protected function getItems() : array
	{
		$result = [];

		$order = $this->payment->getOrder();
		$basket = $order->getBasket();
		$deliveryPrice = $order->getDeliveryPrice();

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$result[] = [
				'quantity'  => $basketItem->getQuantity(),
				'price'     => round($basketItem->getPrice() * 100),
				'tax'       => 6,
				'text'      => $this->convertEncoding($basketItem->getField('NAME')),
			];
		}

		if ($deliveryPrice > 0)
		{
			$result[] = [
				'quantity'  => 1.00,
				'price'     => round($deliveryPrice * 100),
				'tax'       => 6,
				'text'      => 'delivery'
			];
		}

		return $result;
	}

	protected function convertEncoding(string $message) : string
	{
		$isUtf8Config = Main\Application::isUtfMode();

		if ($isUtf8Config) { return $message; }

		return Main\Text\Encoding::convertEncoding($message, 'WINDOWS-1251', 'UTF-8');
	}

	public function refund(): void
	{
		$httpClient = new HttpClient();

		$invoiceId = $this->getPaymentField('PS_INVOICE_ID');
		[$orderId, $reference, $operation] = explode('#', $invoiceId);

		$sector = $this->getParameter('PAYMENT_GATEWAY_SECTOR_ID');
		$password = $this->getParameter('PAYMENT_GATEWAY_PASSWORD');
		$amount = $this->getPaymentAmount();
		$currency = $this->getCurrencyFormatted($this->getPaymentField('CURRENCY'));
		$signature = $this->getSignature([$sector, $orderId, $amount, $currency, $password]);

		$data = [
			'sector'    => $sector,
			'id'        => $orderId,
			'amount'    => $amount,
			'currency'  => $currency,
			'signature' => $signature
		];

		$this->setHeaders($httpClient);
		$url = $this->getUrl('refund');
		$httpClient->post($url, $data);

		$resultData = $this->convertResultData($httpClient->getResult());

		$this->checkResult($httpClient, $resultData);
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
		return null;
	}

	protected function getSignature(array $params): string
	{
		$str = implode('', $params);

		return base64_encode(md5($str));
	}

	protected function getCurrencyFormatted(string $code): int
	{
		$currency = CurrencyClassifier::getCurrency($code, []);

		return (int)$currency['NUM_CODE'] ?: 643;
	}
}