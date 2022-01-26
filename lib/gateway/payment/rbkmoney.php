<?php

namespace YandexPay\Pay\Gateway\Payment;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use YandexPay\Pay\Exceptions\Secure3dRedirect;
use YandexPay\Pay\Gateway\Base;
use YandexPay\Pay\Gateway\Manager;
use YandexPay\Pay\Reference\Concerns\HasMessage;

class Rbkmoney extends Base
{
	use HasMessage;

	protected const STATUS_PAID = 'processed';
	protected const STATUS_CAPTURED = 'captured';
	protected const STATUS_FAILED = 'failed';
	protected const STATUS_REFUNDED = 'refunded';

	protected const TYPE_EVENT_3DS = 'PaymentInteractionRequested';
	protected const TYPE_EVENT_CHANGED = 'PaymentStatusChanged';

	protected const PAYMENT_PAYER_TYPE = 'PaymentResourcePayer';
	protected const PAYMENT_FLOW_TYPE = 'PaymentFlowInstant';

	protected const RESOURCE_PROVIDER = 'YandexPay';
	protected const RESOURCE_PAYMENT_TYPE = 'TokenizedCardData';

	protected const WEBHOOK_TYPE_PROCESSED = 'PaymentProcessed';

	protected const REQUEST_TYPE_POST = 'BrowserPostRequest';

	public function getId() : string
	{
		return Manager::RBKMONEY;
	}

	public function getName() : string
	{
		return 'Rbk money';
	}

	protected function getUrlList() : array
	{
		return [
			'createResource'    => 'https://api.rbk.money/v2/processing/payment-resources',
			'createInvoice'     => 'https://api.rbk.money/v2/processing/invoices',
			'createToken'       => 'https://api.rbk.money/v2/processing/invoices/#INVOICE_ID#/access-tokens',
			'createPay'         => 'https://api.rbk.money/v2/processing/invoices/#INVOICE_ID#/payments',
			'getInvoice'        => 'https://api.rbk.money/v2/processing/invoices?externalID=#EXTERNAL_ID#',
			'getPayment'        => 'https://api.rbk.money/v2/processing/payments?externalID=#EXTERNAL_ID#',
			'invoiceEvents'     => 'https://api.rbk.money/v2/processing/invoices/#INVOICE_ID#/events?limit=100',
			'refund'            => 'https://api.rbk.money/v2/processing/invoices/#INVOICE_ID#/payments/#PAYMENT_ID#/refunds',
		];
	}

	public function extraParams(string $code = '') : array
	{
		return [
			'PAYMENT_GATEWAY_MERCHANT_ID' => [
				'NAME'  => static::getMessage('MERCHANT_ID'),
				'GROUP' => $this->getName(),
				'SORT'  => 600,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => 40,
				],
			],
			'PAYMENT_GATEWAY_SHOP_ID' => [
				'NAME' => static::getMessage('MERCHANT_SHOP_ID'),
				'GROUP' => $this->getName(),
				'SORT' => 650,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => 40,
				],
			],
			'PAYMENT_GATEWAY_API_KEY' => [
				'NAME' => static::getMessage('MERCHANT_API_KEY'),
				'GROUP' => $this->getName(),
				'SORT' => 700,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => 40,
				]
			],
			'WEBHOOK_PROCESSED_KEY' => [
				'NAME' => static::getMessage('WEBHOOK_PROCESSED_KEY'),
				'GROUP'=> $this->getName(),
				'SORT' => 750,
				'INPUT' => [
					'TYPE' => 'STRING',
					'SIZE' => null,
					'MULTILINE' => 'Y',
					'ROWS' => 15,
					'COLS' => 65
				]
			]
		];
	}

	protected function getHeaders(string $key = ''): array
	{
		return [
			'Authorization' => 'Bearer ' . $key,
			'Content-type'  => 'application/json; charset=utf-8',
			'X-Request-ID'  =>  uniqid('', true),
		];
	}

	public function getPaymentIdFromRequest() : ?int
	{
		$result = null;

		$paymentId = $this->request->get('paymentId');

		if ($paymentId !== null) { return $paymentId; }

		$contentSignature = $this->server->get('HTTP_CONTENT_SIGNATURE');

		if ($contentSignature === null) { return null; }

		$signature = $this->getSignatureFromHeader($contentSignature);

		if (empty($signature)) { return null; }

		$decodedSignature = $this->urlSafeBase64decode($signature);

		$content = file_get_contents('php://input');

		$webhookPublicKey = $this->getParameter('WEBHOOK_PROCESSED_KEY');

		if ($this->isVerifySignature($content, $decodedSignature, $webhookPublicKey))
		{
			$payment = $this->request->get('payment');
			$result = (int)$payment['metadata']['externalId'];
		}

		return $result;
	}

	protected function getSignatureFromHeader($contentSignature): string
	{
		return preg_replace("/alg=(\S+);\sdigest=/", '', $contentSignature);
	}

	protected function urlSafeBase64decode($signature)
	{
		return base64_decode(strtr($signature, '-_,', '+/='));
	}

	protected function isVerifySignature($content, $signature, $publicKey): bool
	{
		if (empty($content) || empty($signature) || empty($publicKey)) { return false; }

		$publicKeyId = openssl_get_publickey($publicKey);

		if (empty($publicKeyId)) { return false; }

		$verify = openssl_verify($content, $signature, $publicKeyId, OPENSSL_ALGO_SHA256);

		return ($verify === 1);
	}

	protected function processWebHook(): array
	{
		$result = [];

		$webhook = $this->request->get('eventType');

		if ($webhook !== self::WEBHOOK_TYPE_PROCESSED) { return $result; }

		$shopId = $this->getParameter('PAYMENT_GATEWAY_SHOP_ID');
		$orderId = $this->getOrderId();
		$payment = $this->request->get('payment');
		$invoice = $this->request->get('invoice');

		if (
			$payment['status'] === self::STATUS_PAID
			&& $orderId === $payment['metadata']['orderId']
			&& $invoice['shopID'] === $shopId
		)
		{
			$result = [
				'PS_INVOICE_ID'     => $invoice['id'] . '#' . $payment['id'],
				'PS_STATUS_CODE'    => $payment['status'],
				'PS_SUM'            => $this->getPaymentSum()
			];
		}

		return $result;
	}

	public function startPay() : array
	{
		$result = [];

		if ($this->isSecure3ds())
		{
			$invoiceId = $this->request->get('invoiceId');

			if ($invoiceId !== null)
			{
				$paymentInvoice = $this->checkInvoiceEvents($invoiceId);

				if ($paymentInvoice !== null)
				{
					return [
						'PS_INVOICE_ID'     => $invoiceId . '#' . $paymentInvoice['paymentID'],
						'PS_STATUS_CODE'    => $paymentInvoice['status'],
						'PS_SUM'            => $this->getPaymentSum()
					];
				}
			}
		}

		$fields = $this->processWebHook();

		if (!empty($fields)) { return $fields; }

		[$invoiceId, $invoiceToken] = $this->createInvoice();

		$resource = $this->createPaymentResource($invoiceToken);

		$this->createPayment($invoiceId, $invoiceToken, $resource);

		$this->checkInvoiceEvents($invoiceId);

		return $result;
	}

	protected function createInvoice(): array
	{
		$apiKey = $this->getParameter('PAYMENT_GATEWAY_API_KEY');

		$httpClient = new HttpClient();

		$url = $this->getUrl('createInvoice');

		$data = $this->buildDataInvoice();

		$this->setHeaders($httpClient, $apiKey);

		$httpClient->post($url, $data);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($result, $httpClient->getStatus());

		return [$result['invoice']['id'], $result['invoiceAccessToken']['payload']];
	}

	protected function buildDataInvoice(): string
	{
		$cart = [];

		$order = $this->payment->getOrder();
		$basket = $order->getBasket();
		$deliveryPrice = $order->getDeliveryPrice();

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$cart[] = [
				'product'   => $basketItem->getField('NAME'),
				'quantity'  => $basketItem->getQuantity(),
				'price'     => round($basketItem->getPrice() * 100),
			];
		}

		if ($deliveryPrice > 0)
		{
			$cart[] = [
				'product'   => static::getMessage('ORDER_DELIVERY'),
				'quantity'  => 1,
				'price'     => round($deliveryPrice * 100)
			];
		}

		return Main\Web\Json::encode([
			'shopID'    => $this->getParameter('PAYMENT_GATEWAY_SHOP_ID'),
			'dueDate'   => $this->getDueDate(),
			'currency'  => $this->getPaymentField('CURRENCY'),
			'product'   => static::getMessage('PRODUCT_NAME', ['#ORDER_ID#' => $order->getId()]),
			'cart'      => $cart,
			'metadata'  => [
				'externalId'    => $this->getPaymentId(),
				'paySystemId'   => $this->payment->getPaymentSystemId(),
				'orderId'       => $this->getOrderId()
			],
			'amount'    => $this->getPaymentAmount(),
			'externalID'=> $this->getExternalId()
		]);
	}

	protected function getDueDate(): string
	{
		$order = $this->payment->getOrder();
		$date = $order->getDateInsert();

		$date = new Main\Type\DateTime($date);

		return $date->add('+1 day')->format('Y-m-d\TH:i:s.u\Z');
	}

	protected function createPaymentResource(string $token): array
	{
		$data = $this->buildDataResource();

		$httpClient = new HttpClient();

		$requestUrl = $this->getUrl('createResource');

		$this->setHeaders($httpClient, $token);

		$httpClient->post($requestUrl, $data);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($result, $httpClient->getStatus());

		return $result;
	}

	protected function buildDataResource(): string
	{
		$yandexData = $this->getYandexData();
		unset($yandexData['orderAmount']);

		return Main\Web\Json::encode([
			'paymentTool' => [
				'provider'          => self::RESOURCE_PROVIDER,
				'paymentToolType'   => self::RESOURCE_PAYMENT_TYPE,
				'gatewayMerchantID' => $this->getParameter('PAYMENT_GATEWAY_MERCHANT_ID'),
				'paymentToken'      => $yandexData,
			],
			'clientInfo' => [
				'fingerprint'   => $this->server->get('HTTP_USER_AGENT'),
			],
		]);
	}

	protected function createPayment(string $invoiceId, string $token, array $resourceData): void
	{
		$url = $this->getUrl('createPay', ['#INVOICE_ID#' => $invoiceId]);

		$data = $this->buildDataPayment($resourceData, $invoiceId);

		$httpClient = new HttpClient();

		$this->setHeaders($httpClient, $token);

		$httpClient->post($url, $data);

		$resultData = $this->convertResultData($httpClient->getResult());

		$this->checkResult($resultData, $httpClient->getStatus());
	}

	protected function buildDataPayment(array $resourceData, string $invoiceId): string
	{
		$order = $this->payment->getOrder();

		$propertyCollection = $order->getPropertyCollection();

		$contactInfo = [];
		$email = $propertyCollection->getUserEmail();
		$phone = $propertyCollection->getPhone();

		if ($email !== null)
		{
			$contactInfo['email'] = $email->getValue();
		}

		if ($phone !== null)
		{
			$contactInfo['phoneNumber'] = $phone->getValue();
		}

		return Main\Web\Json::encode([
			'flow'          => [
				'type' => self::PAYMENT_FLOW_TYPE,
			],
			'payer'         => [
				'paymentToolToken'  => $resourceData['paymentToolToken'],
				'paymentSession'    => $resourceData['paymentSession'],
				'payerType'         => self::PAYMENT_PAYER_TYPE,
				'contactInfo'       => $contactInfo,
				'sessionInfo'       => [
					'redirectUrl'   => $this->getRedirectUrl(['invoiceId' => $invoiceId])//$_SESSION['yabackurl']
				]
			],
			'metadata'  => [
				'externalId'    => $this->getPaymentId(),
				'paySystemId'   => $this->payment->getPaymentSystemId(),
				'orderId'       => $this->getOrderId()
			],
		]);
	}

	protected function checkInvoiceEvents(string $invoiceId): ?array
	{
		$result = null;

		sleep(5);

		$resultData = $this->getInvoiceDataEvents($invoiceId);

		$resultPayment = $this->checkStatusPay($resultData);

		if ($resultPayment !== null)
		{
			$result = $resultPayment;
		}

		return $result;
	}

	protected function getInvoiceDataEvents(string $invoiceId) : array
	{
		$httpClient = new HttpClient();

		$apiKey = $this->getParameter('PAYMENT_GATEWAY_API_KEY');

		$url = $this->getUrl('invoiceEvents', ['#INVOICE_ID#' => $invoiceId]);

		$this->setHeaders($httpClient, $apiKey);

		$httpClient->get($url);

		$resultData = $this->convertResultData($httpClient->getResult());

		$this->checkResult($resultData, $httpClient->getStatus());

		return $resultData;
	}

	protected function checkResult(array $resultData, int $status): void
	{
		if (empty($resultData))
		{
			throw new Main\SystemException('GOT EMPTY RESULT WITH STATUS = ' . $status);
		}

		if ($status === 400)
		{
			throw new Main\SystemException(static::getMessage($resultData['code']));
		}

		if ($status === 401)
		{
			throw new Main\SystemException('Authorization error');
		}

		if ($status === 409)
		{
			throw new Main\SystemException($resultData['externalID'] . ': '. $resultData['message']);
		}

		if (isset($resultData['error']) && !empty($resultData['error']))
		{
			throw new Main\SystemException(static::getMessage($resultData['error']['code']) . ': '. $resultData['error']['subError']);
		}
	}

	protected function checkStatusPay(array $resultData): ?array
	{
		$result = null;

		$changes = array_merge(... array_column($resultData, 'changes'));
		$mapType = array_flip(array_column($changes, 'changeType'));

		$payment = $changes[$mapType[self::TYPE_EVENT_CHANGED]] ?? [];
		$secure = $changes[$mapType[self::TYPE_EVENT_3DS]] ?? [];

		if (
			!empty($secure)
			&& (empty($payment) || (string)$payment['paymentID'] !== (string)$secure['paymentID'])
		)
		{
			$requestType = $secure['userInteraction']['request']['requestType'];

			$method = ($requestType === self::REQUEST_TYPE_POST ? 'POST' : 'GET');

			$params = [];

			foreach ($secure['userInteraction']['request']['form'] as $param)
			{
				$params[$param['key']] = $param['template'];
			}

			throw new Secure3dRedirect($secure['userInteraction']['request']['uriTemplate'], $params, false, $method);
		}

		if (!empty($payment))
		{
			if ($payment['status'] === self::STATUS_FAILED && empty($secure))
			{
				throw new Main\SystemException(self::getMessage('FAILED_STATUS'));
			}

			if ($payment['status'] === self::STATUS_REFUNDED)
			{
				throw new Main\SystemException(self::getMessage('REFUNDED_STATUS'));
			}

			if ($payment['status'] === self::STATUS_PAID || $payment['status'] === self::STATUS_CAPTURED)
			{
				$result = $payment;
			}
		}

		return $result;
	}

	protected function convertResultData(string $data): array
	{
		return Main\Web\Json::decode($data);
	}

	public function refund(): void
	{
		$apiKey = $this->getParameter('PAYMENT_GATEWAY_API_KEY');

		[$invoiceId, $paymentId] = explode('#', $this->getPaymentField('PS_INVOICE_ID'));

		$httpClient = new HttpClient();

		$url = $this->getUrl('refund', ['#INVOICE_ID#' => $invoiceId, '#PAYMENT_ID#' => $paymentId]);

		$data = $this->buildDataRefund();

		$this->setHeaders($httpClient, $apiKey);

		$httpClient->post($url, $data);

		$result = $this->convertResultData($httpClient->getResult());

		$this->checkResult($result, $httpClient->getStatus());
	}

	protected function buildDataRefund()
	{
		$index = $this->payment->getInternalIndex();
		$data = $this->request->get('data');
		$comment = $data['PAY_RETURN_COMMENT_' . $index] ?? '';

		return Main\Web\Json::encode([
			'externalID'    => $this->getExternalId(),
			'amount'        => $this->getPaymentAmount(),
			'currency'      => $this->getPaymentField('CURRENCY'),
			'reason'        => Main\Text\Encoding::convertEncodingToCurrent($comment)
		]);
	}

	protected function isSecure3ds() : bool
	{
		return $this->request->get('secure3ds') !== null;
	}
}